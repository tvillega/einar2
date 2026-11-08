.PHONY: stage2 stage2-disk stage2-mount stage2-config stage2-apt-firefox stage2-apt-docker stage2-apt stage2-install stage2-readonly stage2-umount

stage2:
	@echo "==> Starting Stage 2 VM build process..."
	@echo "==> [1/7] Creating Stage 2 overlay disk..."
	@$(MAKE) stage2-disk
	@echo "==> [2/7] Mounting filesystems..."
	@$(MAKE) stage2-mount
	@echo "==> [3/7] Writing system configurations..."
	@$(MAKE) stage2-config
	@echo "==> [4/7] Setting up external APT repositories..."
	@$(MAKE) stage2-apt
	@echo "==> [5/7] Running package installation and chroot setup..."
	@$(MAKE) stage2-install
	@echo "==> [6/7] Setting image as read-only..."
	@$(MAKE) stage2-readonly
	@echo "==> [7/7] Unmounting and cleaning up..."
	@$(MAKE) stage2-umount
	@echo "==> Stage 2 completed successfully: dist/einar2-stage2.img is ready."

stage2-disk:
	@mkdir -p dist
	@if [ ! -f dist/einar2-stage1.img ]; then \
		echo "==> ERROR: dist/einar2-stage1.img not found. Run 'make stage1' first." >&2; \
		exit 1; \
	fi
	@if [ -f dist/einar2-stage2.img ]; then \
		echo "==> ERROR: dist/einar2-stage2.img already exists." >&2; \
		exit 1; \
	fi
	@set -e; \
	trap 'echo "==> ERROR: Stage 2 overlay creation failed."; rm -f dist/einar2-stage2.img; exit 1' ERR; \
	echo "  -> Creating QCOW2 overlay image backed by stage 1..."; \
	qemu-img create -f qcow2 -F qcow2 -b einar2-stage1.img dist/einar2-stage2.img 8G; \
	echo "==> Stage 2 disk overlay created successfully."

stage2-mount:
	@set -e; \
	trap 'echo "==> ERROR: Mounting failed. Rolling back..."; $(MAKE) stage2-umount; exit 1' ERR; \
	modprobe nbd max_part=8; \
	echo "  -> Connecting stage 2 image to /dev/nbd0..."; \
	qemu-nbd --connect=/dev/nbd0 dist/einar2-stage2.img; \
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

stage2-config:
	@set -e; \
	trap 'echo "==> ERROR: Configuration failed."; exit 1' ERR; \
	echo "  -> Configuring initrd and vmlinuz symlink rules..."; \
	{ \
		printf "do_symlinks   = yes\n"; \
		printf "do_bootloader = no\n"; \
	} > dist/mnt/etc/kernel-img.conf; \
	echo "  -> Generating filesystem table (fstab)..."; \
	genfstab -U dist/mnt >> dist/mnt/etc/fstab; \
	echo "  -> Capturing root partition UUID for kernel command line..."; \
	ROOT_UUID=$$(blkid -s UUID -o value /dev/nbd0p2); \
	mkdir -p dist/mnt/etc/kernel; \
	echo "root=UUID=$$ROOT_UUID" > dist/mnt/etc/kernel/cmdline; \
	echo "  -> Configuring hostname and hosts file..."; \
	echo "einar2" > dist/mnt/etc/hostname; \
	echo "127.0.1.1 einar2" >> dist/mnt/etc/hosts; \
	echo "==> System configurations written successfully."

stage2-apt-firefox:
	@set -e; \
	echo "  -> Setting up Mozilla APT repository for native Firefox (.deb)..."; \
	mkdir -p dist/mnt/etc/apt/keyrings; \
	wget -q https://packages.mozilla.org/apt/repo-signing-key.gpg -O- | tee dist/mnt/etc/apt/keyrings/packages.mozilla.org.asc > /dev/null; \
	echo "deb [signed-by=/etc/apt/keyrings/packages.mozilla.org.asc] https://packages.mozilla.org/apt mozilla main" | tee dist/mnt/etc/apt/sources.list.d/mozilla.list > /dev/null; \
	{ \
		printf "Package: firefox*\n"; \
		printf "Pin: origin packages.mozilla.org\n"; \
		printf "Pin-Priority: 1000\n"; \
	} > dist/mnt/etc/apt/preferences.d/mozilla; \
	echo "==> Mozilla/Firefox repository configured."

stage2-apt-docker:
	@set -e; \
	echo "  -> Setting up Docker official repository..."; \
	mkdir -p dist/mnt/etc/apt/keyrings; \
	curl -fsSL https://download.docker.com/linux/ubuntu/gpg -o dist/mnt/etc/apt/keyrings/docker.asc; \
	chmod a+r dist/mnt/etc/apt/keyrings/docker.asc; \
	echo "deb [signed-by=/etc/apt/keyrings/docker.asc] https://download.docker.com/linux/ubuntu resolute stable" | tee dist/mnt/etc/apt/sources.list.d/docker.list > /dev/null; \
	echo "==> Docker repository configured."

stage2-apt:
	@set -e; \
	echo "  -> Blocking snapd and unnecessary packages via apt preferences..."; \
	mkdir -p dist/mnt/etc/apt/preferences.d; \
	{ \
		printf "Package: grub-common grub2-common grub-pc grub-pc-bin grub-gfxpayload-lists\n"; \
		printf "Pin: release *\n"; \
		printf "Pin-Priority: -1\n\n"; \
		printf "Package: snapd\n"; \
		printf "Pin: release *\n"; \
		printf "Pin-Priority: -1\n\n"; \
		printf "Package: cloud-init landscape-common popularity-contest ubuntu-advantage-tools\n"; \
		printf "Pin: release *\n"; \
		printf "Pin-Priority: -1\n"; \
	} > dist/mnt/etc/apt/preferences.d/ignored-packages; \
	echo "  -> Adding base Ubuntu apt repositories..."; \
	{ \
		printf "deb https://mirror.hnd.cl/ubuntu/ resolute        main restricted universe\n"; \
		printf "deb https://mirror.hnd.cl/ubuntu/ resolute-security   main restricted universe\n"; \
		printf "deb https://mirror.hnd.cl/ubuntu/ resolute-updates    main restricted universe\n"; \
	} > dist/mnt/etc/apt/sources.list
	@$(MAKE) stage2-apt-firefox
	@$(MAKE) stage2-apt-docker
	@echo "==> All APT repositories and preferences configured."

stage2-install:
	@set -e; \
	trap 'echo "==> ERROR: Installation/Chroot step failed."; exit 1' ERR; \
	echo "  -> Entering chroot and updating system packages..."; \
	{ \
		printf "export DEBIAN_FRONTEND=noninteractive\n"; \
		printf "export PATH=\"/usr/sbin:/usr/bin:/sbin:/bin\"\n"; \
		printf "apt update && apt upgrade -y\n\n"; \
		printf "echo \"  -> Installing essential system and utility packages...\"\n"; \
		printf "apt install -y linux-image-virtual linux-headers-virtual initramfs-tools systemd-boot curl dhcpcd nano zstd wireshark dnsutils tcpdump ncat socat nmap hping3 lxde-core l3afpad firefox docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin\n\n"; \
		printf "echo \"  -> Setting root password...\"\n"; \
		printf "echo \"root:einar2\" | chpasswd\n"; \
	} | arch-chroot dist/mnt bash -s; \
	echo "==> Package installation and chroot setup completed."

stage2-readonly:
	@set -e; \
	if [ -f dist/einar2-stage2.img ]; then \
		echo "  -> Removing write permissions..."; \
		chmod -w dist/einar2-stage2.img; \
		echo "==> Base image successfully locked."; \
	else \
		echo "==> ERROR: Image file not found." >&2; \
		exit 1; \
	fi

stage2-umount:
	@set -e; \
	echo "  -> Unmounting EFI partition..."; \
	umount -lf dist/mnt/efi 2>/dev/null || true; \
	echo "  -> Unmounting root partition..."; \
	umount -lf dist/mnt 2>/dev/null || true; \
	echo "  -> Disconnecting NBD device..."; \
	qemu-nbd --disconnect /dev/nbd0 2>/dev/null || true; \
	sleep 1; \
	echo "  -> Unloading nbd module..."; \
	rmmod nbd 2>/dev/null || true; \
	echo "==> Unmount and cleanup completed successfully."
