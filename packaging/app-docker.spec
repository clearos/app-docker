
Name: app-docker
Epoch: 1
Version: 2.5.7
Release: 1%{dist}
Summary: Docker
License: GPLv3
Group: ClearOS/Apps
Source: %{name}-%{version}.tar.gz
Buildarch: noarch
Requires: %{name}-core = 1:%{version}-%{release}
Requires: app-base
Requires: app-network

%description
Docker is a software technology providing operating-system-level virtualization also known as containers.

%package core
Summary: Docker - Core
License: LGPLv3
Group: ClearOS/Libraries
Requires: app-base-core
Requires: app-network-core >= 1:2.4.6
Requires: app-mail-routing-core
Requires: app-firewall-core
Requires: docker
Requires: docker-compose

%description core
Docker is a software technology providing operating-system-level virtualization also known as containers.

This package provides the core API and libraries.

%prep
%setup -q
%build

%install
mkdir -p -m 755 %{buildroot}/usr/clearos/apps/docker
cp -r * %{buildroot}/usr/clearos/apps/docker/

install -d -m 0755 %{buildroot}/var/clearos/docker
install -d -m 0755 %{buildroot}/var/clearos/docker/backup
install -d -m 0755 %{buildroot}/var/clearos/docker/project
install -D -m 0644 packaging/10-docker %{buildroot}/etc/clearos/firewall.d/10-docker
install -D -m 0755 packaging/clearos-compose %{buildroot}/usr/sbin/clearos-compose
install -D -m 0644 packaging/docker.php %{buildroot}/var/clearos/base/daemon/docker.php

%post
logger -p local6.notice -t installer 'app-docker - installing'

%post core
logger -p local6.notice -t installer 'app-docker-core - installing'

if [ $1 -eq 1 ]; then
    [ -x /usr/clearos/apps/docker/deploy/install ] && /usr/clearos/apps/docker/deploy/install
fi

[ -x /usr/clearos/apps/docker/deploy/upgrade ] && /usr/clearos/apps/docker/deploy/upgrade

exit 0

%preun
if [ $1 -eq 0 ]; then
    logger -p local6.notice -t installer 'app-docker - uninstalling'
fi

%preun core
if [ $1 -eq 0 ]; then
    logger -p local6.notice -t installer 'app-docker-core - uninstalling'
    [ -x /usr/clearos/apps/docker/deploy/uninstall ] && /usr/clearos/apps/docker/deploy/uninstall
fi

exit 0

%files
%defattr(-,root,root)
/usr/clearos/apps/docker/controllers
/usr/clearos/apps/docker/htdocs
/usr/clearos/apps/docker/views

%files core
%defattr(-,root,root)
%exclude /usr/clearos/apps/docker/packaging
%exclude /usr/clearos/apps/docker/unify.json
%dir /usr/clearos/apps/docker
%dir /var/clearos/docker
%dir /var/clearos/docker/backup
%dir /var/clearos/docker/project
/usr/clearos/apps/docker/deploy
/usr/clearos/apps/docker/language
/usr/clearos/apps/docker/libraries
/etc/clearos/firewall.d/10-docker
/usr/sbin/clearos-compose
/var/clearos/base/daemon/docker.php
