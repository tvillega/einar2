.PHONY: stage1 stage1-disk stage1-filesystem stage1-mount stage1-bootstrap stage1-readonly stage1-umount

stage1:
	@echo "==> Starting Stage 1 VM build process..."
	@echo "==> [1/4] Virtual disk setup..."
	@$(MAKE) stage1-disk
	@echo "==> [2/4] Formatting filesystems..."
	@$(MAKE) stage1-filesystem
	@echo "==> [3/4] Running bootstrap..."
	@$(MAKE) stage1-bootstrap
	@echo "==> [4/4] Setting image as read-only..."
	@$(MAKE) stage1-readonly
	@echo "==> Stage 1 completed successfully: dist/einar2-stage1.img is ready."

stage1-disk:
	@mkdir -p dist
	@if [ -f dist/einar2-stage1.img ]; then \
		echo "==> ERROR: dist/einar2-stage1.img already exists." >&2; \
		exit 1; \
	fi
	@set -e; \
	trap 'echo "==> ERROR: Disk creation or partitioning failed."; rm -f dist/einar2-stage1.img; exit 1' ERR; \
	echo "  -> Creating 8G QCOW2 image..."; \
	qemu-img create -f qcow2 dist/einar2-stage1.img 8G; \
	echo "  -> Loading NBD module..."; \
	modprobe nbd max_part=8; \
	echo "  -> Connecting image to /dev/nbd0..."; \
	qemu-nbd --connect=/dev/nbd0 dist/einar2-stage1.img; \
	echo "  -> Partitioning disk with sfdisk..."; \
	{ \
		printf "label: gpt\n"; \
		printf "size=300M, type=C12A7328-F81F-11D2-BA4B-00A0C93EC93B\n"; \
		printf "type=0FC63DAF-8483-4772-8E79-3D69D8477DE4\n"; \
	} | sfdisk /dev/nbd0; \
	echo "  -> Disconnecting NBD after partitioning..."; \
	qemu-nbd --disconnect /dev/nbd0; \
	echo "==> Disk and partition setup completed."

stage1-filesystem:
	@set -e; \
	modprobe nbd max_part=8; \
	qemu-nbd --connect=/dev/nbd0 dist/einar2-stage1.img; \
	trap 'echo "==> ERROR: Formatting failed."; qemu-nbd --disconnect /dev/nbd0 2>/dev/null || true; exit 1' ERR; \
	echo "  -> Waiting for kernel to recognize partitions..."; \
	partprobe /dev/nbd0 || sleep 1; \
	echo "  -> Formatting ESP partition (/dev/nbd0p1) as VFAT..."; \
	mkfs.vfat -F32 -n ESP /dev/nbd0p1; \
	echo "  -> Formatting ROOT partition (/dev/nbd0p2) as EXT4..."; \
	mkfs.ext4 -L ROOT /dev/nbd0p2; \
	echo "  -> Disconnecting NBD after formatting..."; \
	qemu-nbd --disconnect /dev/nbd0; \
	echo "==> Filesystems successfully formatted."

stage1-mount:
	@set -e; \
	modprobe nbd max_part=8; \
	qemu-nbd --connect=/dev/nbd0 dist/einar2-stage1.img; \
	trap 'echo "==> ERROR: Mounting failed. Rolling back..."; $(MAKE) stage1-umount; exit 1' ERR; \
	echo "  -> Waiting for kernel to recognize partitions..."; \
	partprobe /dev/nbd0 || sleep 1; \
	echo "  -> Creating root mount directory..."; \
	mkdir -p dist/mnt; \
	echo "  -> Mounting root partition..."; \
	mount /dev/nbd0p2 dist/mnt; \
	echo "  -> Creating EFI mount directory inside root..."; \
	mkdir -p dist/mnt/efi; \
	echo "  -> Mounting EFI partition..."; \
	mount /dev/nbd0p1 dist/mnt/efi; \
	echo "==> Filesystems successfully mounted."

stage1-bootstrap:
	@set -e; \
	$(MAKE) stage1-mount; \
	trap 'echo "==> ERROR: Debootstrap failed. Cleaning up..."; $(MAKE) stage1-umount; exit 1' ERR; \
	echo "  -> Starting debootstrap for Ubuntu resolute..."; \
	debootstrap resolute dist/mnt https://mirror.hnd.cl/ubuntu/; \
	echo "==> Bootstrap completed successfully."; \
	$(MAKE) stage1-umount

stage1-readonly:
	@set -e; \
	if [ -f dist/einar2-stage1.img ]; then \
		echo "  -> Removing write permissions..."; \
		chmod -w dist/einar2-stage1.img; \
		echo "==> Base image successfully locked."; \
	else \
		echo "==> ERROR: Image file not found." >&2; \
		exit 1; \
	fi

stage1-umount:
	@set -e; \
	echo "  -> Unmounting EFI partition..."; \
	umount dist/mnt/efi 2>/dev/null || true; \
	echo "  -> Unmounting root partition..."; \
	umount dist/mnt 2>/dev/null || true; \
	echo "  -> Disconnecting NBD device..."; \
	qemu-nbd --disconnect /dev/nbd0 2>/dev/null || true; \
	echo "==> Unmount and cleanup completed successfully."
