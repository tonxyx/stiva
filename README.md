#STIVA

Custom app for a good friend's job. ;)

##Official Documentation

Documentation for the framework can be found on the Phalcon's website.

##Initial STIVA setup

- get vagrant from https://github.com/phalcon/vagrant
- add stiva clone inside vagrant/www/ folder
- execute vagrant up
- after installation run vagrant ssh
- run mysql with "mysql -uroot -p" -> password is empty
- create database stiva
- generate database from /schemas/stiva.sql (mysql -uroot -p stiva < /schemas/stiva.sql)
- create vhost in /etc/hosts (192.168.50.4  vagrant.local)
- visit http://vagrant.local and choose your project

For additional problems feel free to ask. ;)

##Documentation - plan

- run app - user login
- all orders listed in grid
- add new order manually and import new order automatically
- manually you need to enter order data
- automatically you just upload an .csv order file in predefined format
- after upload application should calculate optimal package numbers
- setting for types and package numbers (max) should be saved as a application settings
- you should be able to modify package numbers
- there is also an additional transport items for empty space
- after calculation and confirm you should be able to print all cards out 
- at the end you should be able to print outgoing report

##Database concept

- users (id, username, first name, last name, company, active, password, password salt)
- orders (id, order number, quantity, width, height, date, barcode, ...)
- packages ?

##Package calculation

- there are few types of product
- should use algorithm for placement

## NOTE

The master branch will always contain the latest stable version. If you wish
to check older versions or newer ones currently under development, feel free 
to check older commits.
