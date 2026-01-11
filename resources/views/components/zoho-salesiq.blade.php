@php
    $widgetCode = config('app.zoho_salesiq_widget_code');
@endphp
@if($widgetCode)
<script type="text/javascript">
    var $zoho = $zoho || {};
    $zoho.salesiq = $zoho.salesiq || {
        widgetcode: "{{ $widgetCode }}",
        values: {},
        ready: function () {}
    };
    var d = document;
    var s = d.createElement("script");
    s.type = "text/javascript";
    s.id = "zsiqscript";
    s.defer = true;
    s.src = "https://salesiq.zoho.com/widget";
    var t = d.getElementsByTagName("script")[0];
    t.parentNode.insertBefore(s, t);
</script>
@endif

