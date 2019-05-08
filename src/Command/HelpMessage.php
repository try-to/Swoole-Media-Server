<?php

namespace TrytoMediaServer\Command;

class HelpMessage
{
    public static $logo = <<<'LOGO'
 ______  ____   __ __  ______   ___
|      ||    \ |  |  ||      | /   \
|      ||  D  )|  |  ||      ||     |
|_|  |_||    / |  ~  ||_|  |_||  O  |
  |  |  |    \ |___, |  |  |  |     |
  |  |  |  .  \|     |  |  |  |     |
  |__|  |__|\_||____/   |__|   \___/

LOGO;
    public static $version = 'version: ' . TRYTO_VERSION . PHP_EOL;
    public static $usage   = <<<'USAGE'
Usage:
  tryto [ start | stop | restart | status | reload ] [ -c | --config <configuration_path> | --console ]
  tryto -h | --help
  tryto -v | --version
USAGE;
    public static $desc = <<<'DESC'
Options:
  start                            Start server
  stop                             Shutdown server
  restart                          Restart server
  status                           Show server status
  reload                           Reload configuration
  -h --help                        Display help
  -v --version                     Display version
  -c --config <configuration_path> Specify configuration path
  --console                        Front desk operation
DESC;
}
