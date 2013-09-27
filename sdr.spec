%define name sdr
%define install_dir /var/www/phpwebsite/mod/sdr

Summary:   Student Development Record
Name:      %{name}
Version:   %{version}
Release:   %{release}
License:   GPL
Group:     Development/PHP
URL:       http://phpwebsite.appstate.edu
Source:    %{name}-%{version}-%{release}.tar.bz2
Requires:  php >= 5.0.0, php-gd >= 5.0.0, phpwebsite
Prefix:    /var/www/phpwebsite
BuildArch: noarch

%description
The Student Development Record

%prep
%setup -n %{name}-%{version}-%{release}

%post
/usr/bin/curl -L -k http://127.0.0.1/apc/clear

%install
mkdir -p "$RPM_BUILD_ROOT%{install_dir}"
cp -r * "$RPM_BUILD_ROOT%{install_dir}"

# Default Deletes for clean RPM

rm -Rf "$RPM_BUILD_ROOT%{install_dir}/docs/"
rm -Rf "$RPM_BUILD_ROOT%{install_dir}/.hg/"
rm -f "$RPM_BUILD_ROOT%{install_dir}/.hgtags"
rm -f "$RPM_BUILD_ROOT%{install_dir}/build.xml"
rm -f "$RPM_BUILD_ROOT%{install_dir}/sdr.spec"
rm -f "$RPM_BUILD_ROOT%{install_dir}/phpdox.xml"
rm -f "$RPM_BUILD_ROOT%{install_dir}/cache.properties"

# Clean up crap from the repo tht doesn't need to be in production
rm -Rf "$RPM_BUILD_ROOT%{install_dir}/sdr/mod/sdr/populate"
rm -Rf "$RPM_BUILD_ROOT%{install_dir}/sdr/mod/sdr/utils"


%clean
rm -rf "$RPM_BUILD_ROOT%{install_dir}"

%files
%defattr(-,root,root)
%{install_dir}

%changelog
* Fri Oct 21 2011 Jeff Tickle <jtickle@tux.appstate.edu>
- Made the phpwebsite install more robust, including the theme
- Added Cron Job, but never tested it so it probably won't work
* Thu Jun  2 2011 Jeff Tickle <jtickle@tux.appstate.edu>
- Added build.xml and hms.spec to the repository, prevented these files from installing
- Added some comments
* Thu Apr 21 2011 Jeff Tickle <jtickle@tux.appstate.edu>
- New spec file for HMS, includes phpWebSite
