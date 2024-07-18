<?php
if(!isset($datas[0]))
{
	//echo "(No data)";
	//exit;
    $datas = [];
}
?>
<html>
<head>
<title>Export</title>
</head>
<body>
	    <?php
        $announcement= (array) $datas[0];
        $attributes = array_keys($announcement);
        ?>
<table>
<?php if(isset($header)) if($header){ ?>
    <tr>
            @foreach($attributes as $a)
        <th>
            {{$a}}
        </th>
            @endforeach
    </tr>
<?php } ?>
    @foreach($datas as $data)
    <tr>
        @foreach($attributes as $a)
            <td>
                {{$data->$a}}
            </td>
        @endforeach
    </tr>
    @endforeach
</table>
</body>
</html>