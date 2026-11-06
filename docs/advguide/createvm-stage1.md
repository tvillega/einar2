## Stage 1

Creates a *stage 1* overlay of a minimal Ubuntu.

### Virtual disk

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

### Filesystem

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


(7) Debootstrap an Ubuntu LTS:

```
debootstrap resolute mnt https://mirror.hnd.cl/ubuntu/
```

### Clean up

(1) Umount the `esp` partition:

```
umount mnt/efi
```

(2) Umount the `root` partition:

```
umount mnt
```

(3) Disconnect the nbd:

```
qemu-nbd --disconnect /dev/nbd0
```
