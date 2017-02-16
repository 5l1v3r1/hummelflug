Hummelflug
==========

A utility for arming (creating) many bumblebees (micro EC2 instances)
to attack (load test) targets (web applications). 
Inspired by [Bees with Machine Guns](http://github.com/newsapps/beeswithmachineguns)

Dependencies
------------

* PHP (>=5.5.9) with SSH2 extension
* Composer

Installation
------------

1. Clone the repository or download the zip file.
2. Run `composer install`

Configuration
-------------

Rename the `config.ini.sample` to `config.ini` and
adjust it to your needs.

Usage
-----

### Hummelflug in a Nutshell

At first you want to set up your swarm:
        
        php hummelflug create 

Before you can attack your target you need to wake up your swarm:

        php hummelflug up
        
Now you can attack your target:

        php hummelflug attack <URL>
        
Tired of attacking peaceful servers?

        php hummelflug down
        

The caveat! (PLEASE READ)
-------------------------

If you decide to use Hummelflug, please keep in mind
the following important caveat: they are, more-or-less
a distributed denial-of-service attack in a fancy
package and, therefore, if you point them at any server
you don’t own you will behaving unethically, have your
Amazon Web Services account locked-out, and be liable
in a court of law for any downtime you cause.

You have been warned.

Bugs
----

Please log your bugs on the [Github issues tracker](https://github.com/scheddel/hummelflug/issues).

License
-------
MIT.