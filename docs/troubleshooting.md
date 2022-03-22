[Back to table of contents](./)

# Troubleshooting

## If you don't see 3d models on product page

- [Make sure overrides are enabled](prestashop-config.md#overrides-should-be-enabled)
- In some cases, when you use Prestashop 1.6 after updating the module, you must delete the `/cache/class_index.php` file to make overrides work after update. [More info about deleting the `/cache/class_index.php` file.](http://doc.prestashop.com/display/PS16/Overriding+default+behaviors#Overridingdefaultbehaviors-Manipulatingtheoverridecodemanually)

## If you get a Callback validation error
Ensure that the admin panel URL is reachable by HTTP and does not require HTTP Basic authentication.

[Back to table of contents](./)
