# The macro prerel and the Version and Release tags are set by the builder-scripts
# Also the Source0 URL will be adapted by the builder-scripts when needed
#
# When this is a pre-release, define the prerel field
%define prerel $PRERELEASE
# also need a fsprerel field for a pre-release to get the correct tarball
%if 0%{?prerel:1}
%define fsprerel -%{prerel}
%endif
Name:           filesender
Version:        1.5
Release:        1%{?dist}
Summary:        Sharing large files with a browser

Group:          Applications/Internet
License:        BSD
URL:            http://www.filesender.org/
Source0:        http://repository.filesender.org/releases/%{name}-%{version}%{?fsprerel}.tar.gz
Source2:	%{name}.htaccess
Source3:	%{name}.cron.daily
BuildRoot:      %{_tmppath}/%{name}-%{version}-%{release}-root-%(%{__id_u} -n)
BuildArch: noarch

Requires: httpd
Requires: php >= 5.2.0
Requires: php-xml
Requires: simplesamlphp
Requires: postgresql-server
Requires: php-pgsql

%description
FileSender is a web based application that allows authenticated users to
securely and easily send arbitrarily large files to other users.
Authentication of users is provided through SAML2, LDAP and RADIUS.
Users without an account can be sent an upload voucher by an
authenticated user. FileSender is developed to the requirements of the
higher education and research community.
.
The purpose of the software is to send a large file to someone, have
that file available for download for a certain number of downloads and/or
a certain amount of time, and after that automatically delete the file.
The software is not intended as a permanent file publishing platform.


%prep
%setup -q -n %{name}-%{version}%{?fsprerel}

%build

%install
rm -rf %{buildroot}
%{__mkdir} -p %{buildroot}%{_datadir}/%{name}
%{__mkdir} -p %{buildroot}%{_sysconfdir}/%{name}
%{__mkdir} -p %{buildroot}%{_sysconfdir}/httpd/conf.d
%{__mkdir} -p %{buildroot}%{_sysconfdir}/cron.daily
%{__mkdir} -p %{buildroot}%{_sysconfdir}/php.d
%{__mkdir} -p %{buildroot}%{_localstatedir}/lib/%{name}/files
%{__mkdir} -p %{buildroot}%{_localstatedir}/lib/%{name}/tmp
%{__mkdir} -p %{buildroot}%{_localstatedir}/log/%{name}

%{__cp} -ad ./* %{buildroot}%{_datadir}/%{name}
%{__cp} -p %{SOURCE2} %{buildroot}%{_sysconfdir}/httpd/conf.d/%{name}.conf
%{__cp} -p %{SOURCE3} %{buildroot}%{_sysconfdir}/cron.daily/%{name}
%{__cp} -p ./config-templates/filesender-php.ini %{buildroot}%{_sysconfdir}/php.d/%{name}.ini

%{__cp} -p config/config-dist.php ${RPM_BUILD_ROOT}%{_sysconfdir}/%{name}/config-dist.php
%{__sed} -i "s|'/simplesaml/'|'/simplesamlphp/'|g" ${RPM_BUILD_ROOT}%{_sysconfdir}/%{name}/config-dist.php
%{__cp} -p ${RPM_BUILD_ROOT}%{_sysconfdir}/%{name}/config-dist.php ${RPM_BUILD_ROOT}%{_sysconfdir}/%{name}/config.php

%{__rm} -f %{buildroot}%{_datadir}/%{name}/*.txt
%{__rm} -f %{buildroot}%{_datadir}/%{name}/*.specs

%{__rm} -rf %{buildroot}%{_datadir}/%{name}/config
%{__rm} -rf %{buildroot}%{_datadir}/%{name}/tmp
%{__rm} -rf %{buildroot}%{_datadir}/%{name}/log
%{__rm} -rf %{buildroot}%{_datadir}/%{name}/files

ln -s ../../../..%{_sysconfdir}/%{name} %{buildroot}%{_datadir}/%{name}/config
ln -s ../../..%{_localstatedir}/lib/%{name}/tmp %{buildroot}%{_datadir}/%{name}/tmp
ln -s ../../../..%{_localstatedir}/lib/%{name}/files %{buildroot}%{_datadir}/%{name}/files
ln -s ../../..%{_localstatedir}/log/%{name} %{buildroot}%{_datadir}/%{name}/log

%clean
rm -rf %{buildroot}

%files
%defattr(-,root,root,-)
%doc CHANGELOG.txt  INSTALL.txt  LICENCE.txt  README.txt
%{_datadir}/%{name}/
%dir %{_sysconfdir}/%{name}/
%attr(0640,root,apache) %{_sysconfdir}/%{name}/config-dist.php
%config(noreplace) %attr(0640,root,apache) %{_sysconfdir}/%{name}/config.php
%config(noreplace) %{_sysconfdir}/httpd/conf.d/%{name}.conf
%config(noreplace) %{_sysconfdir}/php.d/%{name}.ini
%config(noreplace) %attr(0755,root,root) %{_sysconfdir}/cron.daily/%{name}
%dir %{_localstatedir}/lib/%{name}/
%dir %attr(0750,apache,apache) %{_localstatedir}/lib/%{name}/tmp
%dir %attr(0750,apache,apache) %{_localstatedir}/lib/%{name}/files
%dir %attr(0750,apache,apache) %{_localstatedir}/log/%{name}


%changelog
* %(date '+%a %b %d %Y') FileSender Development <filesender-dev@filesender.org> %{version}-%{release}
- Release %{version}%{?fsprerel}

* Mon Oct 22 2012 FileSender Development <filesender-dev@filesender.org> 1.5-0.7.rc1
- Release 1.5-rc1

* Wed Jul 25 2012 FileSender Development <filesender-dev@filesender.org> 1.5-0.6.beta4
- Release 1.5-beta4

* Tue May 15 2012 FileSender Development <filesender-dev@filesender.org> 1.5-0.5.beta3
- Release 1.5-beta3

* Wed Apr 25 2012 FileSender Development <filesender-dev@filesender.org> 1.5-0.4.beta2
- Release 1.5-beta2

* Mon Feb 13 2012 FileSender Development <filesender-dev@filesender.org> 1.5-0.1.beta1
- Release 1.5-beta1

* Sat Nov 05 2011 FileSender Development <filesender-dev@filesender.org> 1.1-1
- Release 1.1

* Wed May 11 2011 FileSender Development <filesender-dev@filesender.org> 1.0.1-1
- Release 1.0.1

* Mon Jan 31 2011 FileSender Development <filesender-dev@filesender.org> 1.0-1
- Release 1.0
