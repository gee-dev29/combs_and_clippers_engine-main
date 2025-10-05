<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js">

</script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js">

</script>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

<script>
        $().ready(function () {
                $('.dateField').daterangepicker({
                        singleDatePicker: true,
                        showDropdowns: true,
                        minYear: 2000,
                        maxYear: parseInt(moment().format('YYYY'), 10),
                        "locale": {
                                "format": 'YYYY-MM-DD'
                        },
                        autoApply:true,
                        autoUpdateInput: false,
                });
                $('.dateField').on('apply.daterangepicker', function(ev, picker) {
                        $(this).val(picker.startDate.format('YYYY-MM-DD'));
                    });
        });
</script>