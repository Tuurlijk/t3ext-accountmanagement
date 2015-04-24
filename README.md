# TYPO3 extension: accountmanagement
Manage TYPO3 CMS users from the cli using an extbase command controller

Use with [https://github.com/helhum/typo3_console](https://github.com/helhum/typo3_console) for optimum pleasure.

Clone it
```bash
git clone https://github.com/MaxServ/t3ext-accountmanagement.git accountmanagement
```

Or install it using composer:
```json
{
    "repositories": [
        {
            "type": "git",
            "url": "https://github.com/MaxServ/t3ext-accountmanagement.git"
        }
    ],
    "require": {
        "maxserv/accountmanagement": "*"
    }
}
```

Example:
```bash
$ ./typo3cms user:create --username _cli_scheduler
```

Available commands:

```bash
EXTENSION "ACCOUNTMANAGEMENT":
-------------------------------------------------------------------------------
  user:activate                            Activate a user
  user:deactivate                          Deactivate a user
  user:list                                List backend users
  user:create                              Create a new user
```

More help is available per command:
```bash
$ ./typo3cms help user:create
```

Setting a password does not yet work as that property is currently missing from the Extbase BackendUser model.