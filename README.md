# drive-tester

[![Build Status](https://travis-ci.org/serverzone/drive-tester.svg?branch=master)](https://travis-ci.org/serverzone/drive-tester)

Drive tester utility.

## Runtime requirements

* debian stretch
* PHP >=7.2
* puppet 4.8

## Instalation

```bash
git clone https://git.gitlab.srw.cz/ServerZone.cz/Utilities/drive-tester.git
bin/setup.sh
```

### Update
```bash
git pull origin master
bin/setup.sh
```

## Configuration

Utility use configuration from file `src/config.local.neon`.

1. Copy `src/config.local.neon.template` to `src/config.local.neon`.
2. Set up configuration parameters.

### Parameters

* **storeDir** - Directory path to store smart informations.
* **logFilename** - Log file name with path.

### Services

To send notification to Mattermost uncomment MattermostWebHook service and specify url value.
```yml
    - App\Event\Subscriber\SendNotification\MattermostWebHook('https://mattermost.my-company.net/hooks/xxx')
```

## Usage

Run with drive autodetection:
```bash
bin/console.php drive:test -a
```

Run for specified drive(s):
```bash
bin/console.php drive:test /dev/sdb /dev/sdc
```

**Note**: Not run for system drive!
