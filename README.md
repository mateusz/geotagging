# Geotagging Module

## Maintainer Contact

* Mateusz Uzdowski <mateusz (at) silverstripe (dot) com>

## Requirements

* SilverStripe 2.4 or newer

## Installation

The module comes preconfigured, with extension added to SiteTree to enable
geotagging on *all* pages. If you wish to apply the extension only to some pages
remove it and re-apply:

Object::remove_extension('SiteTree', 'Geotagged'); 
Object::add_extension('X', 'Geotagged');

