<?php
if(sizeof($result)!=0)
{
	?>
		<table class="table tablesaw table-bordered table-hover table-striped mb-0"  data-tablesaw-sortable data-tablesaw-minimap data-tablesaw-mode-switch>
        <thead class="">
            <tr class="blue_Head">
                <th width="20%">Page Name</th>
                <th width="20%">Page URL</th>
                <th width="20%">Page Type</th>
                <th width="10%" class="centerAlign">Status</th>
   
                <th width="15%">Action</th>
            </tr>
        </thead>
		@foreach($result AS $resultkey => $value)

		<?php
			if($value['is_active']==1)
			{
				$status 	=	'Active';
				$class 		=	'';
			}
			else
			{
				$status 	=	'In-active';
				$class 		=	'trInactive';
			}
		?>

			<tr class="<?php echo $class?>">
				<td class="leftAlign">{{$value->product_features_name}}</td>
				<td class="leftAlign">{{$value->feature_url}}</td>
				<td class="leftAlign">Article Page</td>
				<td class="">{{$status}}</td>
				<td class="leftAlign">
					<button class="btn btn-icon btn-icon-only btn-secondary btn-icon-style-4" onclick="editPage(({{$value->product_features_id}}))"title="edit Page"><i class="fa fa-pencil"></i></button>

					<!-- <button class="btn btn-icon btn-icon-only btn-secondary btn-icon-style-4" onclick="deletePage({{$value->product_features_id}})" title="delete Page"><i class="fa fa-trash"></i></button> -->
				</td>
			</tr>
		@endforeach
		</table>

		<script type="text/javascript">
		$(document).ready(function(e){
			$('.PagecountResult').html('&nbsp;(<?php echo ($resultkey+1)?>)');
		})
		</script>

	<?php
}
?>