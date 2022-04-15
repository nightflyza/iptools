# IPTools - useful network tools web-interface
========


# Installation

```
   $ mkdir /usr/local/www/apache24/data/iptools (or where you htdocs root directory is)
   $ cd /usr/local/www/apache24/data/iptools
   $ wget https://github.com/nightflyza/iptools/archive/refs/heads/master.zip
   $ unzip master.zip
   $ mv iptools-master/* ./
   $ rm -fr iptools-master master.zip
   $ chmod -R 777 exports content config
```

After that web-application will be accessible by URL http://yourhost/iptools/

You can perform configuration of some paths and another basic options via editing config/yalf.ini 

# Misc links

  * [Live demo](http://ip.nightfly.biz)
  