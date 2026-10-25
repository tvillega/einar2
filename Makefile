pkgname          = einar2
prefix          ?= /usr/local
exec_prefix      = $(prefix)
bindir           = $(exec_prefix)/bin
sbindir          = $(exec_prefix)/sbin
libdir           = $(exec_prefix)/lib/$(pkgname)
datarootdir      = $(prefix)/share
datadir          = $(datarootdir)/$(pkgname)
sysconfdir       = /etc/$(pkgname)
localstatedir    = /var
runstatedir      = $(localstatedir)/run
docdir           = $(datarootdir)/doc/$(pkgname)

SHELL := /bin/bash

.PHONY: all install uninstall package

all:
	@echo "Ready to install."

install:
	@echo "Installing to $(DESTDIR)$(prefix)..."

	# Create target directories first
	install -d $(DESTDIR)$(bindir)
	install -d $(DESTDIR)$(libdir)
	install -d $(DESTDIR)$(datadir)
	install -d $(DESTDIR)$(docdir)
	install -d $(DESTDIR)$(sysconfdir)

	# Create system sub directories
	install -d $(DESTDIR)/etc/profile.d
	install -d $(DESTDIR)/root

	# Create einar2 sub directories
	install -d $(DESTDIR)$(datadir)/archetypes
	install -d $(DESTDIR)$(datadir)/hooks

	# Copy files to target directories
	install -m 755 bin/*                  $(DESTDIR)$(bindir)/
	install -m 644 share/archetypes/*     $(DESTDIR)$(datadir)/archetypes/
	install -m 644 share/defaults.conf    $(DESTDIR)$(datadir)/
	install -m 644 share/hooks/*          $(DESTDIR)$(datadir)/hooks/
	install -m 644 share/profile.d/*      $(DESTDIR)/etc/profile.d/
	install -m 755 libexec/*              $(DESTDIR)$(libdir)/
	install -m 644 docs/*.md              $(DESTDIR)$(docdir)/


	# Source custom scripts on each exec -it bash
	echo 'for f in /etc/profile.d/*.sh; do [ -r "$$f" ] && . "$$f"; done' >> $(DESTDIR)/root/.bashrc

	# Patch paths on executables
	sed -i 's|/libexec|/lib/$(pkgname)|g' $(DESTDIR)$(bindir)/$(pkgname)
	sed -i 's|/share|/share/$(pkgname)|g' $(DESTDIR)$(bindir)/$(pkgname)
	sed -i 's|/config|/etc/$(pkgname)|g'  $(DESTDIR)$(bindir)/$(pkgname)

package:
	@echo "Building package..."

	rm -rf ./temp-pkg ./einar2-utils.tar.gz
	$(MAKE) install DESTDIR=./temp-pkg prefix=/usr
	tar -czf einar2-utils.tar.gz -C ./temp-pkg .
	rm -rf ./temp-pkg

	@echo "Package ready: ./einar2-utils.tar.gz"
