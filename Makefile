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
systemdsysdir    = $(prefix)/lib/systemd/system

SHELL := /bin/bash

.PHONY: all install uninstall package

all:
	@echo "Ready to install."

install:
	@echo "Installing to $(DESTDIR)$(prefix)..."

	install -d $(DESTDIR)$(bindir)
	install -d $(DESTDIR)$(libdir)
	install -d $(DESTDIR)$(datadir)
	install -d $(DESTDIR)$(docdir)
	install -d $(DESTDIR)$(sysconfdir)
	install -d $(DESTDIR)$(systemdsysdir)

	install -d $(DESTDIR)/etc/profile.d
	install -d $(DESTDIR)$(datadir)/archetypes
	install -d $(DESTDIR)$(datadir)/hooks
	install -d $(DESTDIR)$(docdir)/advguide

	install -m 755 bin/einar2             $(DESTDIR)$(bindir)/einar2
	install -m 755 bin/einard             $(DESTDIR)$(bindir)/einard
	install -m 755 bin/netns              $(DESTDIR)$(bindir)/netns

	install -m 644 etc/profile.d/*        $(DESTDIR)/etc/profile.d/

	install -m 644 share/archetypes/*     $(DESTDIR)$(datadir)/archetypes/
	install -m 644 share/compose.yml      $(DESTDIR)$(datadir)/
	install -m 644 share/defaults.conf    $(DESTDIR)$(datadir)/
	install -m 755 share/hooks/*          $(DESTDIR)$(datadir)/hooks/

	install -m 755 libexec/*              $(DESTDIR)$(libdir)/

	install -m 644 docs/*.md              $(DESTDIR)$(docdir)/
	install -m 644 docs/advguide/*.md     $(DESTDIR)$(docdir)/advguide/

	install -m 644 lib/systemd/system/einard.service $(DESTDIR)$(systemdsysdir)/einard.service

	# Patch paths on executables
	sed -i 's|/libexec|/lib/$(pkgname)|g' $(DESTDIR)$(bindir)/$(pkgname)
	sed -i 's|/share|/share/$(pkgname)|g' $(DESTDIR)$(bindir)/$(pkgname)
	sed -i 's|/config|/etc/$(pkgname)|g'  $(DESTDIR)$(bindir)/$(pkgname)

uninstall:
	@echo "Uninstalling from $(DESTDIR)$(prefix)..."

	rm -f $(DESTDIR)$(bindir)/einar2
	rm -f $(DESTDIR)$(bindir)/einard
	rm -f $(DESTDIR)$(bindir)/netns

	rm -f $(DESTDIR)/etc/profile.d/einar2-utils.sh
	rm -f $(DESTDIR)$(systemdsysdir)/einard.service

	rm -rf $(DESTDIR)$(libdir)
	rm -rf $(DESTDIR)$(datadir)
	rm -rf $(DESTDIR)$(docdir)
	rm -rf $(DESTDIR)$(sysconfdir)

package:
	@echo "Building package..."

	rm -rf ./dist/temp-pkg ./dist/einar2-utils.tar.gz
	$(MAKE) install DESTDIR=./dist/temp-pkg prefix=/usr
	tar -czf dist/einar2-utils.tar.gz -C ./dist/temp-pkg .
	rm -rf ./dist/temp-pkg

	@echo "Package ready: ./dist/einar2-utils.tar.gz"
