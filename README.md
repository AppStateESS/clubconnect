ClubConnect
===========

A system for managing student clubs and organizations.  It makes co-curricular
transcripts as well.  With some work it will integrate with Banner or whatever
else central student management system you have, or it can operate on its own.

It used to be called Student Development Records (SDR), which is why that
appears all throughout the source code. I doubt that legacy name will ever be
entirely removed.

This is a module for [phpWebSite](https://github.com/AppStateESS/phpwebsite).
It should go in phpwebsite/mod/sdr (rename clubconnect to sdr because that is
how it goes).  Go to Boost, click "Install", and let the magic happen.

Using some kind of Single Sign-On through phpWebSite Users module is strongly
recommended.  We like Shibboleth, but you can do LDAP, Cosign, really anything
you can write an authentication script for.

Lovingly handcrafted with [vim](http://www.vim.org).
