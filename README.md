# Magento 2 shipping method for OCA 

This module allow to use [OCA E-Pak](https://www.oca.com.ar/ecommerce_epak_epak/) shipping on Magento 2

## Installation

Use [composer](https://getcomposer.org/) to install Gento_Oca.

```
composer require gento-arg/module-oca
```

Then you'll need to activate the module.

```
bin/magento module:enable Gento_Oca
bin/magento setup:upgrade
bin/magento cache:clean
```

## Configuration

### Setting CUIT number

OCA use `CUIT` to identify his customers and validate contracts. To config `CUIT`, just go to `Stores -> Configuration` and select `Sales -> Tax`, in that form you will see a field `CUIT` under the group `Default Tax Destination Calculation`.


### Create operatories

On the menu `GENTo -> Operatories`, add all the operatories that you want to work with.

#### Fields

* **Name**: This will show to the customer on the shipping method list.
* **Code**: The OCA contract number
* **Active**: Enable or disable the operatory
* **Uses id centro imposicion**: This indicate that the operatory will use branches.
* **Pays on destination branch**: If its true, the amount of shipping will not charge to customer order, but will be informed.

### Branches

The branches will be create automatically, but you can control which one not to use by change the `active` attribute to `no`, or maybe changing some data. 
*Warning: If you change the code it may not work properly.* 

## Uninstall

```
bin/magento module:uninstall Gento_Oca
```

If you used Composer for installation Magento will remove the files and database information. 

## Contributing

Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

Please make sure to update tests as appropriate.

### How to create a PR

1. Fork it
2. Create your feature branch (git checkout -b my-new-feature)
3. Commit your changes (git commit -am 'Add some feature')
4. Push to the branch (git push origin my-new-feature)
5. Create new Pull Request

## License

[MIT](https://choosealicense.com/licenses/mit/)