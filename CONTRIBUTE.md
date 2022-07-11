# Contribution guidelines

**FileSender welcomes all contributions**. It is impossible to make a product like this without the efforts of many
people, so please don't be shy and share your help with us. Even the tiniest contribution can make a difference!

This guidelines briefly explain how to contribute to FileSender in an effective manner, making sure to keep high
quality standards and making it easier for your contributions to make through.

## Team members

We've been lucky to have the help of many people through the years. FileSender wouldn't have reached so far without
them, and [we want to thank them from here](https://docs.filesender.org/filesender/acknowledgements). Unfortunately,
they are so many it is nearly impossible to mention all of them.
[Github can offer a good summary on who has contributed to the project](https://github.com/filesender/filesender/graphs/contributors?from=2012-05-20&type=c).
Big thanks to you all!

## First things first

Before embarking yourself in a contribution, please make sure you are familiar with the way FileSender is written,
the way it works, and what is required or not.

* Make sure to read [the documentation](http://docs.filesender.org/).
* If you have a question about FileSender, please ask in the [development mailing list](https://sympa.uninett.no/lists/filesender.org/info/filesender-dev).
* If you think you have discovered a bug, please check the [issue tracker](https://github.com/filesender/filesender/issues)
and the [pull requests](https://github.com/filesender/filesender/pulls) to verify it hasn't been reported before.

## Contributing code

New features are always welcome provided they will be useful to someone apart from yourself. Please take a look at the
[list of issues](https://github.com/filesender/filesender/issues) to see what people are demanding.

When contributing your code, please follow these guidelines:

* Do not include many changes in every commit. Commits should be focused and address one single problem or feature. By
having **multiple, small commits** instead of few large ones, it is easier to track what you are doing, revert changes
in case of an error and help you out if needed.
* **Be explicit** and write useful comment.
* **Keep things simple**. Avoid big functions, long nested loops or `if` statements.
* Include complete **phpdoc** documentation for every property and method you add. If you change a method or property,
make sure to update the existing *phpdoc* accordingly. Do not forget to document all parameters, returned values and
exceptions thrown.
* Try to keep **backwards-compatibility**. Code that breaks current configurations and installations is difficult to
deploy, and therefore we try to avoid it.
* Add **unit tests** to verify that your code not only works but also keeps working over time. When adding tests, keep
the same directory structure used for regular classes. Try to cover **all your code** with tests. The bigger the test
coverage, the more reliable and better our library is.
* Add proper **documentation** explaining your how to use your new feature or how your code changes things.
* Submit your code as a **pull request** in GitHub, from a branch with a descriptive name in your own fork of the
repository. Add a meaningful, short title, and explain in detail what you did and why in the description of the *PR*.
Add instructions on how to test your code.

Sometimes it can take a long time until we are able to process your pull requests, so don't get discouraged if we don't
respond rightaway. And remember that by following this guideline, you are making it easier for us to analyze your
request, so the process will be smoother and faster. We really appreciate you helping us out, not only with your code,
but also by following this guidelines.

## Reporting bugs

Before reporting a bug, please make sure it is indeed a bug. Check [the documentation](http://docs.filesender.org/)
to verify what the intended behaviour is. Review the [list of issues](https://github.com/filesender/filesender/issues)
and the [pull requests](https://github.com/filesender/filesender/pulls) to see if someone has already reported the
same issue.

Pull requests are definitely more appreciated than plain issue reports, as they are easier and faster to address, but
please, do not hesitate to open an issue if you don't have coding skills or just can't find the bug. It's better to have
just an issue report than nothing!

You can help us diagnose and fix bugs by asking and providing answers to the following questions:

* How can we reproduce the bug?
* Is it reproducible in other environments (for example, on different browsers or devices)?
* Are the steps to reproduce the bug clear? If not, can you describe how you might reproduce it?
* What tags should the bug have?
* How critical is this bug? Does it impact a large amount of users?
* Is this a security issue? If so, how severe is it? How can an attacker exploit it?

## Translations

FileSender is translated to many languages, though it needs constant updates from translators, as well as new
translations to other languages. Translations can be contributed through [POEditor](https://poeditor.com/join/project/RqXr9WBJwU).

Before starting a new translation, decide what style you want to use, whether you want to address the user using a formal
tone, etc. Be coherent and keep that style through all your translations. If there is already a translation and
you want to complete it, make sure to keep the same style used by your fellow translators.

## Documentation

Documentation is in the separate  repository in *markdown* format. You can submit pull requests with fixes. If you
encounter some feature that's not documented, or the documentation does not reflect the real behaviour of the library,
please do not hesitate to open an issue.

Good documentation is key to make things easier for our users!

## Community

You don't feel capable of contributing with your code, but are using FileSender and can share your knowledge and
experience? Please, do so! Join our [users mailing list](https://sympa.uninett.no/lists/filesender.org/info/filesender-dev)
and help other users when you can. Your experience might be valuable for many!
