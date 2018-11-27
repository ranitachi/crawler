<?php

 function errors_for($attribute, $errors)
 {
    return $errors->first($attribute, '<p class="text-danger">:message</p>');
 }

 function set_active($path, $active='active')
 {
    // return Request::is($path) || Request::is($path . '/*') ? $active: '';
    return Request::is($path) || Request::is($path . '/*') ? $active: '';
 }

 function set_active_admin($path, $active='active')
 {
    return Request::is($path) ? $active: '';
 }

function toMonth($m)
{
   $bln=[1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];

   return $bln[$m];
}
?>