.PHONY: stage-recovery
stage-recovery:
	@echo "==> Forcing recovery: unmounting, disconnecting NBD, and unloading module..."
	@umount -lf dist/mnt/efi 2>/dev/null || true
	@umount -lf dist/mnt 2>/dev/null || true
	@qemu-nbd --disconnect /dev/nbd0 2>/dev/null || true
	@rmmod nbd 2>/dev/null || true
	@echo "==> Recovery completed. NBD subsystem is reset."
