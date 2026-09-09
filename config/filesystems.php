<?php
return ['default'=>'local','disks'=>['local'=>['driver'=>'local','root'=>storage_path('app/private'),'throw'=>false],'public'=>['driver'=>'local','root'=>storage_path('app/public'),'url'=>'/storage','visibility'=>'public','throw'=>true]],'links'=>[public_path('storage')=>storage_path('app/public')]];
