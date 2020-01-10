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