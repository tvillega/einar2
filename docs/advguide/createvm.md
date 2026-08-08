## Create VM

Create a `qcow2` image:

```
qemu-img create -f qcow2 einar2.img 15G
```

Enable Network Block Device (nbd) on the kernel:

```
modprobe nbd max_part=8
```

Connect the image to a nbd:

```
qemu-nbd --connect=/dev/nbd0 einar2.img
```

Now `/dev/nbd0` will act as a 15G disk device on the system.

Partition the disk:

```
fdisk /dev/nbd0
```

Follow the utility instructions for:

* create a new empty GPT partition table
* add a new partition (1G)
* add a new partition (rest of device)
* write table to disk and exit


Create a `fat32` filesystem on `*p1`:

```
mkfs.vfat -F32 -n ESP /dev/nbd0p1
```

Create a `ext4` filesystem on `*p2`:

```
mkfs.ext4 -L ROOT /dev/nbd0p2
```

Create a mount point:

```
mkdir -p mnt/efi
```

Mount the `root` partition:

```
mount /dev/nbd0p2 mnt
```

Mount the `esp` partition:

```
mount /dev/nbd0p1 mnt/efi
```

Debootstrap an Ubuntu LTS:

```
debootstrap resolute mnt https://mirror.hnd.cl/ubuntu/
```

Block installation of unnecessary packages:

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

Add repositories:

```
cat > mnt/etc/apt/sources.list << EOF
deb https://mirror.hnd.cl/ubuntu/ resolute           main restricted universe
deb https://mirror.hnd.cl/ubuntu/ resolute-security  main restricted universe
deb https://mirror.hnd.cl/ubuntu/ resolute-updates   main restricted universe
EOF
```

Enable creation of symlinks for `initrd` and `vmlinuz`:

```
cat > mnt/etc/kernel-img.conf << EOF
do_symlinks   = yes
do_bootloader = no
EOF
```

Update filesystem table:

```
genfstab -U mnt >> mnt/etc/fstab
```

Add kernel parameter for bootloader:

```
cat > mnt/etc/kernel/cmdline << EOF
root=UUID=<nbd0p2-uuid>
EOF
```

Chroot into the new system:

```
arch-chroot mnt
```

Populate the local repositories and update the system packages:

```
apt update && apt upgrade -y
```

Install essential packages for a proper VM:

```
apt install -y \
  linux-{,image-,headers-}virtual \
  initramfs-tools \
  systemd-boot \
  dhcpcd \
  nano \
  zstd
```

Set a hostname:

```
echo "einar2" > /etc/hostname && echo "127.0.1.1 einar2" >> /etc/hosts
```

Set `root` password as `einar2`:

```
passwd
```

Now exit the chroot.

Umount the `esp` partition:

```
umount mnt/efi
```

Umount the `root` partition:

```
umount mnt
```

Disconnect the nbd:

```
qemu-nbd --disconnect /dev/nbd0
```

Convert the image to a VirtualBox-compatible image:

```
qemu-img convert -f qcow2 -O vdi einar2.img einar2.vdi
```

Alternatively, run it raw from qemu:

> This assumes the existence of an OVMF firmware `einar2-uefi.fd`.
> You can grab one from the `edk2-ovmf` package in ArchLinux.

```
qemu-system-x86_64 -enable-kvm -cpu host -m 4G -smp cores=2,threads=2,sockets=1,maxcpus=4 -vga qxl \
  -device virtio-serial-pci \
  -spice port=5930,disable-ticketing=on \
  -device virtserialport,chardev=spicechannel0,name=com.redhat.spice.0 \
  -chardev spicevmc,id=spicechannel0,name=vdagent \
  -drive if=pflash,format=raw,file=einar2-uefi.fd \
  -drive file=einar2.img,format=qcow2

```

Then the VM is available through `spice://localhost:5930`.

If your image doesn't boot, then you need to re-generate the initrd
by explicitly specifing the kernel image as it read your host kernel instead:

```
PATH=/usr/sbin:$PATH
update-initramfs -c -k <real-kernel-version-as-seen-in-/boot>
```

Install a desktop environment:

```
apt install lxde-core
```

Install firefox (multi-step, see resources).

Install docker (multi-step, see resources).

### Resources

* https://www.baeldung.com/linux/mount-qcow2-image
* https://semjonov.de/posts/2021-09/minimal-ubuntu-installation-with-debootstrap/
* https://www.baeldung.com/linux/qemu-uefi-boot
* https://wiki.archlinux.org/title/QEMU
* https://docs.openstack.org/image-guide/convert-images.html
* https://arraybolt3.substack.com/p/making-hyper-minimal-ubuntu-virtual
* https://askubuntu.com/questions/1360515/is-the-linux-firmware-package-actually-required-on-a-xen-vps
* https://support.mozilla.org/en-US/kb/install-firefox-linux
