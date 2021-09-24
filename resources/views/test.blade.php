<head>
    <title></title>
    <style type="text/css">
        body
        {
            font-family: Arial;
            font-size: 10pt;
        }
    </style>
</head>
<body>
<table border="0" cellpadding="0" cellspacing="0">
    <tr>
        <td>
            From:
        </td>
        <td>
            <input type="text" id="txtFrom" />
        </td>
        <td>
            &nbsp;
        </td>
        <td>
            To:
        </td>
        <td>
            <input type="text" id="txtTo" />
        </td>
    </tr>
</table>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.6/jquery.min.js" type="text/javascript"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js"
        type="text/javascript"></script>
<link href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/start/jquery-ui.css"
      rel="Stylesheet" type="text/css" />
<script type="text/javascript">
    $(function () {
        $("[id*=txtFrom]").datepicker({
            minDate: new Date(),
            onSelect: function (selected) {
                var dt = new Date(selected);
                dt.setDate(dt.getDate() + 1);
                $("[id*=txtTo]").datepicker("option", "minDate", dt);
            }
        });
        $("[id*=txtTo]").datepicker({
            onSelect: function (selected) {
                var dt = new Date(selected);
                dt.setDate(dt.getDate() - 1);
                $("[id*=txtFrom]").datepicker("option", "maxDate", dt);
            }
        });
    });
</script>
</body>
</html>
