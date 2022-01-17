---
title: Contributing to FileSender
---

# Branches

Filesender hosts its code on github. For each major release there is a
development branch which takes all new code via pull requests. When a
release is made the changes on that development branch are considered
locally to make a single patch which is then fed into the master
branch just prior to the release.

This arrangement of development and master branches was desired by the
Filesender board.

For the 2.x series changes should be made on a branch of development and a pull
request made for the development branch on the Filesender github project.

For the 3.x bootstrap releases the development branch is called
development3. The respective master branch is called master3.

This naming convention will allow future major releases to be made and
avoid confusion about which git branch will match which major release.

As of the end of 2021 the 3.x series is still in prerelease so changes
should be made on the 2.x series where possible and will be ported to
the 3.x series. The obvious case where this does not apply is where
the Bootstrap UI is to be updated where the PR should be made against
development3.

