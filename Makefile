pkgname          = einar2
prefix          ?= /usr/local
exec_prefix      = $(prefix)
bindir           = $(exec_prefix)/bin
sbindir          = $(exec_prefix)/sbin
libdir           = $(exec_prefix)/lib/$(pkgname)
datarootdir      = $(prefix)/share
datadir          = $(datarootdir)/$(pkgname)
sysconfdir       = $(prefix)/etc/$(pkgname)
localstatedir    = $(prefix)/var
runstatedir      = $(localstatedir)/run
docdir           = $(datarootdir)/doc/$(pkgname)

SHELL := /bin/bash

.PHONY: all install uninstall

all:
	@echo "Ready to install."

install:
	@echo "Installing to $(DESTDIR)$(PREFIX)..."

	# Create target directories first
	install -d $(DESTDIR)$(bindir)
	install -d $(DESTDIR)$(libdir)
	install -d $(DESTDIR)$(datadir)
	install -d $(DESTDIR)$(docdir)
	install -d $(DESTDIR)$(sysconfdir)

        # Create sub directories
	install -d $(DESTDIR)$(sysconfdir)/archetypes
	install -d $(DESTDIR)$(datadir)/hooks

	# Copy files to target directories
	install -m 755 bin/*                  $(DESTDIR)$(bindir)/
	install -m 644 config/archetype.d/*   $(DESTDIR)$(sysconfdir)/archetypes/
	install -m 644 share/defaults.conf    $(DESTDIR)$(datadir)/
	install -m 644 share/hooks/*          $(DESTDIR)$(datadir)/hooks/
	install -m 755 libexec/*              $(DESTDIR)$(libdir)/
	install -m 644 docs/*.md              $(DESTDIR)$(docdir)/

	# Patch paths on executables
	sed -i 's|/libexec|/lib/$(pkgname)|g' $(DESTDIR)$(bindir)/$(pkgname)
	sed -i 's|/share|/share/$(pkgname)|g' $(DESTDIR)$(bindir)/$(pkgname)
	sed -i 's|/config|/etc/$(pkgname)|g'  $(DESTDIR)$(bindir)/$(pkgname)
	sed -i 's|/archetype.d|/archetypes|g' $(DESTDIR)$(bindir)/$(pkgname)

