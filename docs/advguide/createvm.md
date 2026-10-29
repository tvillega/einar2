## Create VM

Start by cloning the repository:

```
git clone https://github.com/tvillega/einar2.git
```

Now enter the `dist/` directory before proceeding:

```
mkdir -p dist/ && cd dist
```

Steps are grouped into *stages*, each stage creates an overlay
on top of the previously constructed image. This helps saving
bandwith and time as a misconfigured overlay can just be destroyed
and re-built starting from a previous one.

### Stage 1

Creates a *stage 1* overlay with a minimal Ubuntu LXDE.

#### Virtual disk

(1) Create the `qcow2` image:

```
qemu-img create -f qcow2 einar2-stage1.img 8G
```

(2) Enable Network Block Device (nbd) on the kernel:

```
modprobe nbd max_part=8
```

(3) Connect the image to a nbd:

```
qemu-nbd --connect=/dev/nbd0 einar2-stage1.img
```

Now `/dev/nbd0` will act as a 8G disk device on the system.

(4) Partition the disk:

```
fdisk /dev/nbd0
```

Follow the utility instructions for:

* create a new empty GPT partition table
* add a new partition (300M)
* add a new partition (rest of device)
* write table to disk and exit

#### Filesystem

(1) Create a `fat32` filesystem on `*p1`:

```
mkfs.vfat -F32 -n ESP /dev/nbd0p1
```

(2) Create a `ext4` filesystem on `*p2`:

```
mkfs.ext4 -L ROOT /dev/nbd0p2
```

(3) Create a mount point for `ROOT`:

```
mkdir -p mnt
```

(4) Mount the `root` partition:

```
mount /dev/nbd0p2 mnt
```

(5) Create a mount point for `ESP`:

```
mkdir -p mnt/efi
```

(6) Mount the `esp` partition:

```
mount /dev/nbd0p1 mnt/efi
```

#### Installation

(1) Debootstrap an Ubuntu LTS:

```
debootstrap resolute mnt https://mirror.hnd.cl/ubuntu/
```

(2) Block installation of unnecessary packages:

```
cat > mnt/etc/apt/preferences.d/ignored-packages << EOF
Package: grub-common grub2-common grub-pc grub-pc-bin grub-gfxpayload-lists
Pin: release *
Pin-Priority: -1

Package: snapd cloud-init landscape-common popularity-contest ubuntu-advantage-tools
Pin: release *
Pin-Priority: -1
EOF
```

(3) Add repositories:

```
cat > mnt/etc/apt/sources.list << EOF
deb https://mirror.hnd.cl/ubuntu/ resolute           main restricted universe
deb https://mirror.hnd.cl/ubuntu/ resolute-security  main restricted universe
deb https://mirror.hnd.cl/ubuntu/ resolute-updates   main restricted universe
EOF
```

(4) Enable creation of symlinks for `initrd` and `vmlinuz`:

```
cat > mnt/etc/kernel-img.conf << EOF
do_symlinks   = yes
do_bootloader = no
EOF
```

(5) Update filesystem table:

```
genfstab -U mnt >> mnt/etc/fstab
```

(6) Add kernel parameter for bootloader:

```
cat > mnt/etc/kernel/cmdline << EOF
root=UUID=<nbd0p2-uuid>
EOF
```

#### Configuration

(1) Chroot into the new system:

```
arch-chroot mnt
```

(2) Populate the local repositories and update the system packages:

```
apt update && apt upgrade -y
```

(3) Install essential packages for a proper Einar2 VM:

```
apt install -y \
  linux-{,image-,headers-}virtual \
  initramfs-tools \
  systemd-boot \
  dhcpcd \
  nano \
  zstd \
  wireshark \
  dnsutils \
  tcpdump \
  ncat \
  socat \
  nmap \
  hping3 \
  lxde-core
```

(4) Set a hostname:

```
echo "einar2" > /etc/hostname && echo "127.0.1.1 einar2" >> /etc/hosts
```

(5) Set `root` password as `einar2`:

```
passwd
```

#### Clean up

(1) Exit the chroot.

```
exit
```

(2) Umount the `esp` partition:

```
umount mnt/efi
```

(3) Umount the `root` partition:

```
umount mnt
```

(4) Disconnect the nbd:

```
qemu-nbd --disconnect /dev/nbd0
```

### Stage 2

Creates a *stage 2* overlay that adds `docker` & `firefox`.

#### Preparation

(1) Create the `qcow2` image:

```
qemu-img create -f qcow2 -F qcow2 -b einar2-stage1.img einar2-stage2.img
```

(2) Connect the image to a nbd:

```
qemu-nbd --connect=/dev/nbd0 einar2-stage2.img
```

Now /dev/nbd0 will act as the *stage 2* 8G disk device on the system.

(3) Create a mount point for `ROOT`:

```
mkdir -p mnt
```

(4) Mount the `root` partition:

```
mount /dev/nbd0p2 mnt
```

(5) Create a mount point for `ESP`:

```
mkdir -p mnt/efi
```

(6) Mount the `esp` partition:

```
mount /dev/nbd0p1 mnt/efi
```

(7) Chroot into the *stage 2* system:

```
arch-chroot mnt
```

#### Installation

(1) Install firefox:

> Multi-step as it fetches the `.deb` and not the `snapd`.

* https://support.mozilla.org/en-US/kb/install-firefox-linux

(2) Install docker:

> Multi-step as it adds GPG keys and a repository.

* https://docs.docker.com/engine/install/ubuntu/

#### Clean up

(1) Exit the chroot:

```
exit
```

(2) Umount the `esp` partition:

```
umount mnt/efi
```

(3) Umount the `root` partition:

```
umount mnt
```

(4) Disconnect the nbd:

```
qemu-nbd --disconnect /dev/nbd0
```

### Stage 3

Creates a *stage 3* overlay that adds `einar2-utils` and configures it.

(1) Create the `qcow2` image:

```
qemu-img create -f qcow2 -F qcow2 -b einar2-stage2.img einar2-stage3.img
```

(2) Create a script to start the VM:

> This assumes the existence of an OVMF firmware `einar2-uefi.fd`.
> You can grab one from the `edk2-ovmf` package in ArchLinux.

```
cat > einar2.sh << EOF
qemu-system-x86_64 -enable-kvm -cpu host -m 4G -smp cores=2,threads=2,sockets=1,maxcpus=4 -vga qxl \
  -device virtio-serial-pci \
  -spice port=5930,disable-ticketing=on \
  -device virtserialport,chardev=spicechannel0,name=com.redhat.spice.0 \
  -chardev spicevmc,id=spicechannel0,name=vdagent \
  -drive if=pflash,format=raw,file=einar2-uefi.fd \
  -drive file=einar2-stage3.img,format=qcow2
EOF
```

The VM will be available through `spice://localhost:5930`.

<!---
Convert the image to a VirtualBox-compatible image:

```
qemu-img convert -f qcow2 -O vdi einar2.img einar2.vdi
```

-->

### Resources

* https://www.baeldung.com/linux/mount-qcow2-image
* https://semjonov.de/posts/2021-09/minimal-ubuntu-installation-with-debootstrap/
* https://www.baeldung.com/linux/qemu-uefi-boot
* https://wiki.archlinux.org/title/QEMU
* https://docs.openstack.org/image-guide/convert-images.html
* https://arraybolt3.substack.com/p/making-hyper-minimal-ubuntu-virtual
* https://askubuntu.com/questions/1360515/is-the-linux-firmware-package-actually-required-on-a-xen-vps
