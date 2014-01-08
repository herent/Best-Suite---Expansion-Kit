Best Suite Expansion Kit
========================

Best Suite : Core is designed as a system to allow developers to quickly and simply
set up sophisticated page editing applications. You could get by with as little as
a package controller and still get a full-featured editing interface. 

The intent is to take things that used to take days or weeks to create and do them
in a matter of minutes of hours.

This package is intended for developers, it does not create a usable application
on it's own. The code is fully commented, so you can see exactly how to extend
it to your own applications. 

You may use this package to create systems for your clients, or the marketplace.
However, all of those applications must require a license for Best Suite : Core.
You may not modify or re-distribute the core.

Subjects Covered 
----------------
### Package Controller

* How to verify that the core system is installed
* Creating a new editing page rather than the one from th core for complete control over
	the form's layout
* Adding a new page manager and editors to the dashboard
* Registering your application page types with the core
	* Specify custom search and listing elements
	* Specify custom editing page
* The basics of setting up composer for your page types

### Page Types

* Fixing the "<- Page Types" to point back to the page manager instead of the default
	location. 
* Replacing edit button with something that links to the proper editing page
* How to validate a page before publishing

### Editing Form / Composer Page

* Creating a tabbed interface
* Custom placement of all blocks and attributes

Additional Demonstrations
=========================

The [github version](https://github.com/herent/Best-Suite---Expansion-Kit) has a few
more branches to demonstrate other ways to extend the core.

### [Bare Minimum](https://github.com/herent/Best-Suite---Expansion-Kit/tree/bare_minimum).

This example shows how simple the creation of new page management interfaces can be.
The only files that are needed are the package controller and the installation options 
element.

And, technically, the installation element is not needed unless you need user input
for your application. Setting up the interface can be done completely in the installation,
no need for custom dashboard pages, page type controllers, blocks, etc. Of course, 
you can use those if you need them, as shown in the next branch.

### [Advanced Setup](https://github.com/herent/Best-Suite---Expansion-Kit/tree/advanced_setup).

This example shows a few more options. 

* There is a custom list block type for the pages we install. It allows us to use
	the page type even though it's internal. Normally, this would be hidden from
	the built in page list block by concrete5 itself. It also has the option to 
	display pages under multiple other pages, not just one.
* Installation and setup of attributes explicitly for this application
* Validation workarounds for select and file attributes because they don't quite 
	work with the built in validation functions on their models in context of
	concrete5

TODO List
=========

* Show more examples of how custom search interfaces can be used. Right now, it
	really just shows how to include files from your package, but it would be 
	good to show how to create new forms that match the application better, and
	add new buttons that do things to the pages in the results view
* Update the core to have a permissions category to allow people to specify what
	users and groups can access the managers. Then show examples of how to set
	that up in the package install
* Possibly a real-world example (FAQ or something) to make it a little clearer how
	this package is supposed to be used. But since this is primarily aimed at 
	developers that know how to program in concrete5, that's a bit lower priority
* Continue to expand the documentation and comments to make things even clearer

Contributing
============

If you would like to contribute to this project, feel free to fork and submit pull
requests on github. The core system will not be publicly available there, but can
be opened up to select people that need to modify the core in order to get some
extensions to work. 

There are probably a lot of ways that this system could be improved, it seems like 
the best programs are created out of the work of multiple people and not just one.

Credits
=======
This package and the Best Suite : Core system has been created by Jeremy Werst.

jeremy.werst@gmail.com
www.werstnet.com
