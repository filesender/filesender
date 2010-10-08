Name:           filesender
Version:        0.1.16.1
Release:        1%{?dist}
Summary:        Sharing large files with a browser

Group:          Applications/Internet
License:        custom?
URL:            http://www.assembla.com/spaces/file_sender/documents
Source0:        http://filesender-dev.surfnet.nl/nightly/%{name}-%{version}.tar.gz
Source1:	%{name}-config.php
Source2:	%{name}.htaccess
BuildRoot:      %{_tmppath}/%{name}-%{version}-%{release}-root-%(%{__id_u} -n)
BuildArch: noarch

Requires: httpd
Requires: php
Requires: php-pgsql
Requires: simplesamlphp

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
%setup -q

%build

%install
rm -rf %{buildroot}
%{__mkdir} -p %{buildroot}%{_datadir}/%{name}
%{__mkdir} -p %{buildroot}%{_sysconfdir}/%{name}
%{__mkdir} -p %{buildroot}%{_sysconfdir}/httpd/conf.d
%{__mkdir} -p %{buildroot}%{_localstatedir}/lib/%{name}/files
%{__mkdir} -p %{buildroot}%{_localstatedir}/lib/%{name}/tmp
%{__mkdir} -p %{buildroot}%{_localstatedir}/log/%{name}

%{__cp} -ad ./* %{buildroot}%{_datadir}/%{name}
%{__cp} -p %{SOURCE2} %{buildroot}%{_sysconfdir}/httpd/conf.d/%{name}.conf
%{__cp} -p %{SOURCE1} %{buildroot}%{_sysconfdir}/%{name}/config.inc.php

%{__rm} -f %{buildroot}%{_datadir}/%{name}/*.txt
%{__rm} -f %{buildroot}%{_datadir}/%{name}/*.specs

%{__rm} -r %{buildroot}%{_datadir}/%{name}/config/config.php
%{__rm} -rf %{buildroot}%{_datadir}/%{name}/tmp
%{__rm} -rf %{buildroot}%{_datadir}/%{name}/log
%{__rm} -rf %{buildroot}%{_datadir}/%{name}/www/files

ln -s ../../../..%{_sysconfdir}/%{name}/config.inc.php %{buildroot}%{_datadir}/%{name}/config/config.php
ln -s ../../..%{_localstatedir}/lib/%{name}/tmp %{buildroot}%{_datadir}/%{name}/tmp
ln -s ../../../..%{_localstatedir}/lib/%{name}/files %{buildroot}%{_datadir}/%{name}/www/files
ln -s ../../..%{_localstatedir}/log/%{name} %{buildroot}%{_datadir}/%{name}/log

%clean
rm -rf %{buildroot}

%files
%defattr(-,root,root,-)
%doc CHANGELOG.txt  INSTALL.txt  LICENCE.txt  README.txt
%{_datadir}/%{name}/
%dir %{_sysconfdir}/%{name}/
%config(noreplace) %{_sysconfdir}/%{name}/config.inc.php
%config(noreplace) %{_sysconfdir}/httpd/conf.d/%{name}.conf
%dir %{_localstatedir}/lib/%{name}/
%dir %attr(0750,apache,apache) %{_localstatedir}/lib/%{name}/tmp
%dir %attr(0750,apache,apache) %{_localstatedir}/lib/%{name}/files
%dir %attr(0750,apache,apache) %{_localstatedir}/log/%{name}



%changelog
* Thu Sep 2 2010 Gijs Molenaar <gijs@pythonic.nl> %{Version}-1
- first release
