---
title: Contributing to FileSender
---

# Branches

The release cycle is a little bit different for FileSender than
other open source projects. Releases are not made directly from active
branches but instead changes are collected and applied in a single
patch to a branch where releases are made from.

Filesender hosts its code on github. For each major release there is a
development branch which takes all new code via pull requests. When a
release is made the changes on that development branch are considered
locally to make a single patch which is then fed into the master
branch just prior to the release. 

This arrangement of development and master branches was desired by the
Filesender board.

For the 3.x series changes should be made on a branch of development and a pull
request made for the development3 branch on the Filesender github project.

This naming convention will allow future major releases to be made and
avoid confusion about which git branch will match which major release.

You should make changes against the development3 branch and pull requests
against that branch.
