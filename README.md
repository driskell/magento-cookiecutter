# Cookie Cutter for Magento

Fix broken Magento cookies.

## Overview

Cookie Cutter will cleanup duplicated cookies on a visitor's machine to prevent them from encountering login issues.

This can happen if there was a previous bad cookie domain configuration. For example, if the cookie domain was originally set to example.com and is then changed to www.example.com, login can become broken for some users as Magento will read from one cookie but write to the other.

## Installation

The recommended installation method is to use composer. Alternatively, you can just copy the files into your Magento installation as you would any other extension.

Add the following to your `composer.json` and then run `composer require driskell/magento-cookiecutter`.

```json
    "repositories": [
        ...
        {
            "type": "vcs",
            "url": "https://github.com/driskell/magento-cookiecutter"
        }
    ]
```

## Configuration

The following configuration options are currently available in System > Configuration > Driskell > Cookie Cutter.

Option | Description
--- | ---
Enable | Enables the cookie cutter
