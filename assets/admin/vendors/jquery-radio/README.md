# radiosforbuttons
jQuery plugin for transforming radio-buttons in true beautiful Bootstrap buttons.
### [Demo](http://fiatjaf.github.com/radiosforbuttons/demo.html)
### [Page](http://fiatjaf.github.com/radiosforbuttons/)
## Requirements
The requirements are
+ [jQuery](https://developers.google.com/speed/libraries/devguide#jquery)
+ [Bootstrap](http://twitter.github.com/bootstrap/) (but it really needs only the css for buttons, if you're not already using the entire framework at your project, get only the [bootstrap-buttons.min.css](https://raw.github.com/fiatjaf/radiosforbuttons/master/bootstrap-buttons.min.css) file)
+ the _jquery.radioforbuttons.js_ file, either [minified](https://raw.github.com/fiatjaf/radiosforbuttons/master/jquery.radiosforbuttons.min.js) or [not](https://raw.github.com/fiatjaf/radiosforbuttons/master/jquery.radiosforbuttons.js) (1.9k vs 2.9k).

Just include them all.
## Usage
To use you must have a set of radio-buttons (defined by `<input type="radio">`) with `name`, `value`, unique `ids` and a label (defined by `<label for="id"></label>`) for each one. All these elements must be inside a `div` (or some other block element), which will be, by its time, called on the plugin invocation. The `div` can be anywhere inside a `form`. 
The `html` of the button will be the content of the label for each button. The radio-buttons will be preserved at the DOM, hidden, and their `checked` properties altered on each click at the resulting buttons. It is their value which will be sent to the server on any form submit.
Simple example:
```
<!DOCTYPE html>
<link rel="stylesheet" href="bootstrap-buttons.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min.js"></script>
<script src="jquery.radioforbuttons.js"></script>

<form>
  <div class="default">
    <input type="radio" name="which" value="this" id="this"><label for="this">this</label>
    <input type="radio" name="which" value="that" id="that"><label for="that">that</label>
  </div>
</form>

<script>
  $( '.default' ).radiosforbuttons()
</script>
```
### Group
Change the `group` option from the default `true` to `false` if you don't want the buttons to be in bootstrap button group (as in the `.btn-group` class).
### Vertical
If you want stacked options, just mark option `vertical` as true:
```
$('#radios_div').radiosforbuttons({ vertical: true })
```
The widths of the vertically stacked buttons will be equalized unless you set `autowidth` to `false`.
### Colors
As you know, [bootstrap buttons](http://twitter.github.com/bootstrap/base-css.html#buttons) have colors. You can color the radio buttons in two ways:
#### Calling `radiosforbuttons` with the color or buttonstyle options setted
Just set the option you want to use with the value of the color you will want for all the buttons. Both the [bootstrap button classes names](http://twitter.github.com/bootstrap/base-css.html#buttons) or simply a color name:
```
$('#radios_div').radiosforbuttons({ color: 'black' })
$('#radios_div').radiosforbuttons({ buttonstyle: 'btn-inverse' })
```
#### Using `data` properties on the `input` element
You can assign color to buttons using the `data` properties `button-color` or `button-bootstrap-class`, the values are the same for the example above:
```
<div id="directions">
    <input type="radio" name="direction" data-color="blue" id="north" value="north" ><label for="north"></label>
    <input type="radio" name="direction" data-button-bootstrap-class="btn-danger" id="south" value="south" ><label for="south"></label>
    <input type="radio" name="direction" data-color="green" id="east" value="east" ><label for="east"></label>
    <input type="radio" name="direction" data-button-bootstrap-class="warning" id="west" value="west" ><label for="west"></label>
</div>
```
### Margin
Because of a difference of 2px (I think) on the button margins on Chrome and Firefox which bootstrap apparently doesn't cover, I added this property so you could make the buttons of the group nearer or farther. However, due to other improvement isn't necessary to change it, unless you want to make your buttons farther, then it's a cool thing. Just do:
```
$('#radios_div').radiosforbuttons({ margin: 4 })
```
It also works for vertically stacked buttons.
## Reference
<table>
    <tr>
        <th>option</th>
        <th>type</th>
        <th>default</th>
    </tr>
    <tr>
        <td>group</td>
        <td>boolean</td>
        <td>true</td>
    </tr>
    <tr>
        <td>color</td>
        <td>string</td>
        <td>white</td>
    </tr>
    <tr>
        <td>buttonstyle</td>
        <td>string</td>
        <td>''</td>
    </tr>
    <tr>
        <td>vertical</td>
        <td>boolean</td>
        <td>false</td>
    </tr>
    <tr>
        <td>autowidth</td>
        <td>boolean</td>
        <td>true</td>
    </tr>
    <tr>
        <td>margin</td>
        <td>integer</td>
        <td>0</td>
    </tr>
</table>
