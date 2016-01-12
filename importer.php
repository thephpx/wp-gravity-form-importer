<?php
/**
 * Developer: faisal ahmed <thephpx@gmail.com>
 */
set_time_limit(0);

include('../wp-config.php');
//1 = contact us
//5 = free catalog
//6 = home tanning bed
//8 = commercial tanning bed
//6 = home tanning bed

$form_id = 5;
$file = 'freecatalog-form.csv';

$db = mysqli_connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);

$sql = "SELECT * FROM `wp_rg_form_meta` WHERE `form_id` = '".$form_id."'";

$query = mysqli_query($db,$sql);

$row = mysqli_fetch_assoc($query);

$form = json_decode($row['display_meta']);

//$file = fopen("freecatalog","r");
$file = fopen($file,"r");

$form_labels = array();
foreach($form->fields as $fld)
{
    $form_labels[] = $fld->label;
}

$labels = fgetcsv($file);

$leads = array();
while(! feof($file))
{
    $row = fgetcsv($file);

    $leads[] = array();
    $leads[count($leads)-1]['form_id']  = $form_id;
    $leads[count($leads)-1]['status']   = 'active';

    for($i=0;$i<count($row);$i++)
    {
        if(in_array($labels[$i],$form_labels))
        {
            $leads[count($leads)-1]['details'][] = $row[$i];
        }else{
            if($labels[$i] == "Entry Date")
            {
                $leads[count($leads)-1]['date_created'] =  date("Y-m-d H:i:s", $row[$i]);
            }
            if($labels[$i] == "Source Url")
            {
                $leads[count($leads)-1]['source_url'] =  $row[$i];
            }
        }
    }
}
fclose($file);

echo '<pre>';
print_r($labels);
print_r($leads);
print_r($form->fields);
die();

$start = 0;
$end = 1;

$end = count($leads);

for($i=$start;$i<$end;$i++)
{
    //echo count($leads[$i]);
    if(count($leads[$i]) == 5){

        $query1 = 'INSERT INTO `wp_rg_lead` SET ';

        foreach($leads[$i] as $key=>$val){
            if($key != 'details')
            {
                $query1 .= '`'.$key.'` = "'.$val.'", ';
            }
        }
        echo $query1 = rtrim($query1,', ');
        echo '<br><br>';
        mysqli_query($db,$query1);

        $latest_id = mysqli_insert_id($db);

        $query2 = 'INSERT INTO `wp_rg_lead_detail` SET ';

        for($j=0;$j<count($leads[$i]['details']);$j++)
        {
            $ld_query = $query2.' `form_id` = "'.$form_id.'", `lead_id` = "'.$latest_id.'", `field_number` = "'.($j+1).'", `value` = "'.$leads[$i]['details'][$j].'"';
            echo $ld_query;
            mysqli_query($db,$ld_query);
        }

    }
}

mysqli_close($db);
