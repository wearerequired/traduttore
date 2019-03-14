---
layout: default
title: GlotPress Project Configuration
nav_order: 4
---

# Project Setup

When you successfully installed Traduttore, it's time to set up your first project.

We suggest following the [GlotPress manual](https://glotpress.blog/the-manual/creating-a-new-project/) for creating a new project.

The one field we care about in this step is the `Source file URL` field. You can use this field to have a link created to the source code for the translators in the string editor.

In addition to that, `Source file URL` is the field Traduttore uses to detect your repository.

If your code is hosted in the `acme/my-awesome-plugin` repository on GitHub, you would enter the following URL into this field:

```
https://github.com/acme/my-awesome-plugin/blob/master/%file%#L%line%
```

Similarly, the `Source file URL` would look as follows for a GitLab project:

```
https://gitlab.com/acme/my-awesome-plugin/blob/master/%file%#L%line%
```

For projects hosted on Bitbucket, the format is slightly different:

```
https://bitbucket.org/acme/my-awesome-plugin/src/master/%file%#%file%-%line%
```

Mark the project as "Active" for the language packs to be created. The translation strings will continue to be updated with code changes even when the project is set as inactive.

## Repository Configuration

Depending on where your project is hosted, you need to follow different steps to support automatic translation updates. Please read the according documentation:

* [Bitbucket Repository Configuration](bitbucket.md)
* [GitHub Repository Configuration](github.md)
* [GitLab Repository Configuration](gitlab.md)
* [Self-managed Repository Configuration](self-managed.md)
