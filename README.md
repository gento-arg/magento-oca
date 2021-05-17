# Magento 2 shipping method for OCA

# Tabla de contenido

* [Instalación](#Instalación)
* [Configuración](#Configuración)
    * [Tienda](#Configuración de tienda)
    * [Método](#Configuración de método de envío)
    * [Operatorias](#Operatorias)
* [Desinstalar](#Desinstalar)
* [Contributing](#Contributing)

Este módulo permite utilizar [OCA E-Pak](https://www.oca.com.ar/ecommerce_epak_epak/) como un método de envío en Magento
2

## Instalación

Usar [composer](https://getcomposer.org/) para instalar Gento_Oca.

```
composer require gento-arg/module-oca
```

Luego, es necesario activar el módulo y actualizar la base de datos.

```
bin/magento module:enable Gento_Oca
bin/magento setup:upgrade
bin/magento cache:clean
```

## Configuración

### Configuración de tienda

Este paso sólo es requerido en caso de querer generar las etiquetas de envío. En el menú `Stores -> Configuration`, en
la sección `General -> General` y el grupo`Store information`, es requerido completar los siguientes campos:

- Store name
- Store phone number

Luego, en la sección `Sales -> Shipping Settings`, en el grupo `Origin`:

- Street address
- City
- Zip/Postal code
- Country

### Configuración de método de envío

En las configuraciónes de métodos de envío, sección `Sales -> Shipping Methods` o `Sales -> Delivery methods`
dependendienddo la versión de Magento, OCA utililza el `CUIT` para identificar a sus clientes a fin de validar los
contratos y estimar los costos de envío. A continuación se explican los diferentes campos de configuración:

- **CUIT:** (*) Dato del titular del contrato.
- **Titulo:** Es el titulo que aparecerá junto a las diferentes operatorias en el checkout y en la información de envío.
- **Account number:** (**) Es un numero de cuenta que provee OCA como parte del contrato de ePak.
- **Username:** (**) Usuario de ePak.
- **Password:** (**) Password de ePak.
- **Days to send:** Días para informarle a OCA al momento de generar la orden de retiro.
- **Days to send (Extra):** Estos días no serán informados a OCA pero se visualizarán en el carro en caso de
  que `Show days to send` esté activado.
- **Show days to send:** En caso de activarse, además del nombre del método de envío se le informará al cliente el
  tiempo en el paquete será despachado.
- **Reception time:** Rango horario en el que OCA debe realizar el
- **Confirm:** En caso de estar en `No`, la orden de retiro debe confirmarse desde el panel de OCA.
- **Disabled Postal codes:** En caso de necesitar deshabilitar ciertos códigos postales, se pueden listar en este campo,
  uno por línea.
- **Branch description:** En formato de template, indica la información que debería verse en el pedido en caso de
  seleccionar retiro en sucursal.
- **Oep WebService URL:** URL para comunicarse con servicios
  OEP. `http://webservice.oca.com.ar/oep_tracking_test/Oep_Track.asmx` para test
  y `http://webservice.oca.com.ar/oep_tracking/Oep_Track.asmx` para producción.
- **Epak WebService URL:** URL para comunicarse con servicios de
  ePak. `http://webservice.oca.com.ar/epak_tracking_test/Oep_TrackEPak.asmx` para test
  y `http://webservice.oca.com.ar/epak_tracking/Oep_TrackEPak.asmx` para producción.
- **Tracking URL:** Ésta URL será a la que se le concatenará el número de seguimiento para rastreo. Por
  defecto: `https://www5.oca.com.ar/ocaepakNet/Views/ConsultaTracking/TrackingConsult.aspx?numberTracking=`. Tener en
  cuenta que OCA suele cambiar esta URL.
- **Min box volume:** (***) Para calcular los costos de envío, OCA requiere que se le indique el volumen de lo que se va
  a enviar, en caso de no tener atributos en los productos con los que se pueda calcular el volumen, se utilizará este
  valor.
- **Product (width|height|length) attribute:** Atributo del producto que se utilizará para calcular el volumen.
- **Unit product attribute:** Unidad de medida en la que está expresada la dimensión del producto.

_* Valor requerido para el cálculo de costo de envío_

_** Valor requerido para impresión de etiquetas_

_*** Valor requerido en caso de no tener atributos de dimensiones en los productos_

### Operatorias

En el menú `GENTo -> Operatories` se deben agregar las operatorias con las que se desea trabajar.

#### Campos

* **Name**: Este es el título que verá el cliente para seleccionar.
* **Code**: Código interno de OCA.
* **Uses branches**: Indica si la operatoria es de retiro en sucursal.
* **Pays on destination branch**: En caso de ser positivo, el monto será informado el cliente pero no le generará una
  deuda.

### Sucursales

Las sucursales se crean automáticamente con una sincronización de cron. Una vez que fueron creadas, es posible
deshabilitarlas o incluso cambiar ciertos datos.
*Atención: Si se cambia el código de la sucursal, puede que no funcione correctamente.*

## Desinstalar

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
