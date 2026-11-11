## Create VM

Steps are grouped into *stages*, each stage creates an overlay
on top of the previously constructed image. This helps saving
bandwith and time as a misconfigured overlay can just be destroyed
and re-built starting from a previous one.

(1) Start by cloning the repository:

```
git clone https://github.com/tvillega/einar2.git $CLONEDIR
```

### Base ovelays

For your convenience, `Makefile` automates the first two overlays.
If one of the steps fails, use `make stage-recovery`, remove the
half-baked overlay and check the documentation to proceed manually.

(1) Make the *Stage 1* overlay:

```
make stage1
```

(2) Make the *Stage 2* overlay:

```
make stage2
```

### Stage 3

Creates a *stage 3* overlay that adds and setups `einar2-utils`.

#### Preparation

(1) Create the `qcow2` image:

```
qemu-img create -f qcow2 -F qcow2 -b einar2-stage2.img einar2-stage3.img
```

(2) Create a script to start the VM:

> This assumes the existence of an OVMF firmware `einar2-uefi.fd`.
> You can grab one from the `edk2-ovmf` package in ArchLinux.

```
cat > dist/einar2.sh << EOF
qemu-system-x86_64 -enable-kvm -cpu host -m 4G -smp cores=2,threads=2,sockets=1,maxcpus=4 -vga qxl \
  -device virtio-serial-pci \
  -spice port=5930,disable-ticketing=on \
  -device virtserialport,chardev=spicechannel0,name=com.redhat.spice.0 \
  -chardev spicevmc,id=spicechannel0,name=vdagent \
  -drive if=pflash,format=raw,file=dist/einar2-uefi.fd \
  -drive file=dist/einar2-stage3.img,format=qcow2 \
  -netdev user,id=net0 \
  -device virtio-net-pci,netdev=net0
EOF
```

(3) Generate an `einar2-utils` tarball:

```
make package
```

(4) Generate an Einar2 docker image:

```
make image
```

Now you'll have the following files inside `dist/`:

```
dist/
├── einar2-image.tar.gz
├── einar2.sh
├── einar2-stage1.img
├── einar2-stage2.img
├── einar2-stage3.img
├── einar2-uefi.fd
├── einar2-utils.tar.gz
└── mnt

2 directories, 7 files
```

(5) Start an Einar2 VM instance on another terminal:

> The VM will be available through `spice://localhost:5930`.

```
cd $CLONEDIR/dist
einar2.sh
```

(6) Start a basic web server on another terminal:

> It will be accessible inside the VM through `10.0.2.2:8000`.

```
cd $CLONEDIR/dist
python -m http.server
```

#### Installation

(1) Enter the Einar2 VM from `spice://localhost:5930`:

> The user is `root` and the password is `einar2`.

(2) Bootstrap `einar2-utils` and setup Einar2 VM:

```
bootstrap
```

#### Configuration

(1) Do all your customizations in this step.

(2) Trim all empty space to shrink the release image:

> This must always be the last step after you finish configurations.

```
fstrim -av
```

(7) Shut down the VM.

### Stage 4

This stage creates a ready-to-use VM to export.

#### Distribution

(1) Create a release image from the stage3 image:

> This will merge all overlays on the final image.

```
qemu-img convert -p -m 8 -O qcow2 -c -o compression_type=zstd,cluster_size=64k einar2-stage3.img einar2-$TAG.img
```

(2) Create a **Virtual Box compatible** disk:

> This is not a full VM, only the disk to create it.

```
qemu-img convert -p -m 8 -O vdi einar2-$TAG.img einar2-$TAG.vdi
```

(3) Continue configuration on Virtual Box:

1. Enable UEFI and **disable** secure boot.
2. Set 2GB of RAM and 2 cores (it doesn't need more).
3. Install the Guest Additions from the ISO for better integration.
  a. You'll need to install `bzip2`, `gcc`, `make` and `perl` I think.
4. Export the whole VM to a `einar2-<tag>.ova`.

The final image is about `2.3GB`.

### Resources

* https://www.baeldung.com/linux/mount-qcow2-image
* https://semjonov.de/posts/2021-09/minimal-ubuntu-installation-with-debootstrap/
* https://www.baeldung.com/linux/qemu-uefi-boot
* https://wiki.archlinux.org/title/QEMU
* https://docs.openstack.org/image-guide/convert-images.html
* https://arraybolt3.substack.com/p/making-hyper-minimal-ubuntu-virtual
* https://askubuntu.com/questions/1360515/is-the-linux-firmware-package-actually-required-on-a-xen-vps
