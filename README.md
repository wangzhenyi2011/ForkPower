# fork-power
A simple CLI tool for ensuring that a given php script runs continuously

# example
vim index.php,write
```php
$config = [
"uid"=>"40",
"gid"=>"40",
"runLogPath"=>dirname(__FILE__).DIRECTORY_SEPARATOR."runLog",
];
new ForkPower\ForkPower($config,$argv);
```

vim test.php
```php
sleep(6000);
```

# start
		php index.php start test.php >>test.log
#stop
		php index.php stop test.php
#restart
		php index.php restart test.php >>test.log
#status(get all phpfile status)
		php index.php status




