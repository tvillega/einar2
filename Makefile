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

	install -d $(DESTDIR)$(bindir)
	install -d $(DESTDIR)$(libdir)
	install -d $(DESTDIR)$(datadir)
	install -d $(DESTDIR)$(docdir)
	install -d $(DESTDIR)$(sysconfdir)

	install -d $(DESTDIR)/etc/profile.d
	install -d $(DESTDIR)$(datadir)/archetypes
	install -d $(DESTDIR)$(datadir)/hooks
	install -d $(DESTDIR)$(docdir)/advguide

	install -m 755 bin/einar2             $(DESTDIR)$(bindir)/einar2
	install -m 755 bin/einard             $(DESTDIR)$(bindir)/einard
	install -m 755 bin/netns              $(DESTDIR)$(bindir)/netns

	install -m 644 share/archetypes/*     $(DESTDIR)$(datadir)/archetypes/
	install -m 644 share/defaults.conf    $(DESTDIR)$(datadir)/
	install -m 644 share/hooks/*          $(DESTDIR)$(datadir)/hooks/
	install -m 644 share/profile.d/*      $(DESTDIR)/etc/profile.d/

	install -m 755 libexec/*              $(DESTDIR)$(libdir)/

	install -m 644 docs/*.md              $(DESTDIR)$(docdir)/
	install -m 644 docs/advguide/*.md     $(DESTDIR)$(docdir)/advguide/

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

	rm -rf $(DESTDIR)$(libdir)
	rm -rf $(DESTDIR)$(datadir)
	rm -rf $(DESTDIR)$(docdir)
	rm -rf $(DESTDIR)$(sysconfdir)

package:
	@echo "Building package..."

	rm -rf ./temp-pkg ./einar2-utils.tar.gz
	$(MAKE) install DESTDIR=./temp-pkg prefix=/usr
	tar -czf einar2-utils.tar.gz -C ./temp-pkg .
	rm -rf ./temp-pkg

	@echo "Package ready: ./einar2-utils.tar.gz"
