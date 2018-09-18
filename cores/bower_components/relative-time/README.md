`<relative-time>` receives a time string, and optionally formatting and timezone
options, and converts it into another time string or, by default, relative time string

Examples:

```html
<!-- displays relative time, e.g: "5 minutes ago" -->
<relative-time time=[[mytime]]></relative-time>

<relative-time input-format="M/D/YY h:mm:ss A"
               time="4/6/17 2:34:58 PM UTC"
               input-timezone="UTC"
               output-format="fromNow"></relative-time>
```

where `mytime` is any time object accepted by [moment.js](http://momentjs.com/docs/#/parsing/) by default

Note that this element assumes moment.js and moment-timezone.js are imported.
The element makes no attempt to import them on its own.
