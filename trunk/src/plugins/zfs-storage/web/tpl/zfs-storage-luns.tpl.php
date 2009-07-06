<h1>iSCSI Luns of ZFS zpool {zpool_name} on storage {storage_name}</h1>
{lun_table}
<form action="{formaction}" method="GET">
<h4>Add new Lun on ZFS zpool {zpool_name}</h4>
<div style="float:left;">
{zfs_lun_name}
{zfs_lun_size}
</div>
{hidden_zpool_name}
{hidden_zfs_storage_id}
<div style="text-align:center;">{submit}</div>
</form>