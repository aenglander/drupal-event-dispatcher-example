# Drupal Symfony Event Dispatcher Example

Example Drupal module for The Infinite Wonder of the Symfony Event Dispatcher talk at Drupalcon 2016.

## Licensing

See [license](./LICENSE.txt) file for licensing information

## What's It All About

The following files are avialable:

* [src/BlackListService.php](./src/BlackListService.php) - A simple service that identifies all IP addresses that are
not `127.0.0.1` as blacklisted.

* [src/EventSubscriber.php](./src/EventSubscriber.php) - A service that subscribes to `kernel.request` events
and utilizes the _blacklist service_ to determine if a requesting IP should get an access denied response. It will
only be denied if the request is not a _Sub Request_ and is not requesting an admin URL.

* [drupal_event_dispatcher_example.services.yml](./drupal_event_dispatcher_example.services.yml) Services YAML file
for the example that wires all of the services together and registers the event subscriber via the proper tag.

* [drupal_event_dispatcher_example.info.yml](./drupal_event_dispatcher_example.info.yml) Module YAML file.

## How To See It In Action

1. Install the plugin _(Under Custom)_

2. Navigate to the home page 

3. Get an access denied
