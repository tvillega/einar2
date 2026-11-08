## Stage 2

Creates a *stage 2* overlay with required packages.

### Preparation

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

### Configuration

(1) Block installation of unnecessary packages:

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

(2) Add repositories:

```
cat > mnt/etc/apt/sources.list << EOF
deb https://mirror.hnd.cl/ubuntu/ resolute           main restricted universe
deb https://mirror.hnd.cl/ubuntu/ resolute-security  main restricted universe
deb https://mirror.hnd.cl/ubuntu/ resolute-updates   main restricted universe
EOF
```

(3) Enable creation of symlinks for `initrd` and `vmlinuz`:

```
cat > mnt/etc/kernel-img.conf << EOF
do_symlinks   = yes
do_bootloader = no
EOF
```

(4) Update filesystem table:

```
genfstab -U mnt >> mnt/etc/fstab
```

(5) Add kernel parameter for bootloader:

```
cat > mnt/etc/kernel/cmdline << EOF
root=UUID=<nbd0p2-uuid>
EOF
```

### Installation

(1) Chroot into the new system:

```
arch-chroot mnt
```

(2) Populate the local repositories and update the system packages:

```
apt update && apt upgrade -y
```

(3) Install essential packages:

```
apt install -y \
  linux-{,image-,headers-}virtual \
  initramfs-tools \
  systemd-boot \
  curl \
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
  lxde-core \
  l3afpad
```

(4) Install firefox:

> Multi-step as it fetches the `.deb` and not the `snapd`.

* https://support.mozilla.org/en-US/kb/install-firefox-linux

(5) Install docker:

> Multi-step as it adds GPG keys and a repository.

* https://docs.docker.com/engine/install/ubuntu/

(6) Set a hostname:

```
echo "einar2" > /etc/hostname && echo "127.0.1.1 einar2" >> /etc/hosts
```

(5) Set `root` password as `einar2`:

```
passwd
```

### Clean up

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
