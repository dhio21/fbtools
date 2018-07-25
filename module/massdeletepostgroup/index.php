<h3 class="post-title">
	Delete Post Group <?php $groupname = !empty($_GET['groupname']) ? "<b>".$_GET['groupname']."</b>" : 'Member'; echo $groupname  ?>
</h3>
<div class="post-meta">
	<span>
		Menghapus Postingan digrup secara bersamaan (Harus sebagai administrator)
	</span>            
</div>

<div class="post-content">

	<!-- content -->
	<form class='formtablecheckbox' method="post">
		<?php if (!empty($_GET['groupid'])): ?>
			<br/><label><b>Pilih Anggota yang ingin anda tambahkan pertemanan</b></label>
			<table class="tablecheckbox">
				<thead>
					<tr>
						<th></th>
						<th>Nama</th>
						<th>Isi Status</th>
						<th>Tanggal Publish</th>
						<th>Type</th>
						<th>URL</th>
					</tr>
				</thead>
				<tbody>
					<?php  					
					$url = "https://graph.facebook.com/{$_GET['groupid']}/feed?fields=type,created_time,from,message,story,status_type&limit={$_GET['limit']}&access_token={$_SESSION['token']}";

					$curl = file_get_contents_curl($url);
					$result = json_decode($curl);
					?>
					<?php
					foreach ($result->data as $row) {		
						$message = !empty($row->message) ? $row->message : @$row->story;
						$message = !empty($message) ? $message : @$row->status_type;
						echo "
						<tr>
							<td style='width:5%'>".$row->id."</td>
							<td>".$row->from->name."</td>
							<td title='".htmlentities($message)."'>".truncate($message,30)."</td>
							<td>".date('Y-m-d H:i:s', strtotime($row->created_time))."</td>
							<td>".$row->type."</td>
							<td><a target='_blank' href='https://fb.com/".$row->id."'><button type='button'>Kunjungi</button></a></td>
						</tr>
						";
					}
					?>
				</tbody>
			</table>	
			<br/><label>Delay Proses : </label><br/>
			<select name="delayprocess" class="chosen" style="min-width:200px">
				<option value="1">1 detik</option>
				<option value="5">5 detik</option>
				<option value="15">15 detik</option>
				<option value="30">30 detik</option>
				<option value="60">1 menit</option>
				<option value="180">3 menit</option>		
				<option value="300">5 menit</option>		
			</select><br/><br/>
			<input type="submit" value="Submit">
		<?php else: ?>		
			<br/><label><b>Limit Anggota : </b></label>
			<br/><input type="text" name="limit" value="10">
			<br/><br/><label><b>Pilih Grup yang anda miliki untuk mengambil data anggotanya :</b></label>
			<table class="tableUser">
				<thead>
					<tr>
						<th>Nama Group</th>
						<th>Jumlah Anggota</th>
						<th>Action</th>
					</tr>
				</thead>
				<tbody>
					<?php  
					$limit = 10;
					$url = "https://graph.facebook.com/me/groups?fields=name,members.limit(0).summary(true),administrator&access_token={$_SESSION['token']}";

					$curl = file_get_contents_curl($url);
					$result = json_decode($curl);
					?>
					<?php
					foreach ($result->data as $row) {
						if ($row->administrator) {
							echo "
							<tr>
								<td>".$row->name."</td>
								<td>".number_format($row->members->summary->total_count)."</td>
								<td><button onclick='getgroup(\"".$row->name."\",\"".$row->id."\")' type='button'>Pilih</button></td>
							</tr>
							";
						}		
					}
					?>
				</tbody>
			</table>	
		<?php endif ?>
	</form>

	<table>
		<thead id="theader"></thead>
		<tbody id="tresult"></tbody>
		<tfoot id="tloader"></tfoot>
	</table>

</div>

<script type="text/javascript">
	function getgroup(groupname,groupid){	
		var limit = $("input[name='limit']").val();		
		location.href = '?module=massdeletepostgroup&groupname=' + groupname + '&groupid=' + groupid + '&limit=' + limit;
	}

	$(document).ready(function(){

		$('.formtablecheckbox').on('submit', function(e){
			e.preventDefault();
			var btn = $("input[type='submit']");
			var tloader = $('#tloader');
			btn.prop('disabled',true);
			btn.val('in Progress Execute : ' + $('input[type="hidden"]').length + ' Process');

			tloader.html('<tr><td>Loading <img src="data:image/gif;base64,R0lGODlhKwALAPAAAKrD2AAAACH5BAEKAAEAIf4VTWFkZSBieSBBamF4TG9hZC5pbmZvACH/C05FVFNDQVBFMi4wAwEAAAAsAAAAACsACwAAAjIMjhjLltnYg/PFChveVvPLheA2hlhZoWYnfd6avqcMZy1J14fKLvrEs/k+uCAgMkwVAAAh+QQBCgACACwAAAAAKwALAIFPg6+qw9gAAAAAAAACPRSOKMsSD2FjsZqEwax885hh3veMZJiYn8qhSkNKcBy4B2vNsa3pJA6yAWUUGm9Y8n2Oyk7T4posYlLHrwAAIfkEAQoAAgAsAAAAACsACwCBT4OvqsPYAAAAAAAAAj1UjijLAg9hY6maalvcb+IPBhO3eeF5jKTUoKi6AqYLwutMYzaJ58nO6flSmpisNcwwjEfK6fKZLGJSqK4AACH5BAEKAAIALAAAAAArAAsAgU+Dr6rD2AAAAAAAAAJAVI4oy5bZGJiUugcbfrH6uWVMqDSfRx5RGnQnxa6p+wKxNpu1nY/9suORZENd7eYrSnbIRVMQvGAizhAV+hIUAAA7"/></td></tr>').fadeIn();

			var lastResponseLength = false;
			var ajaxRequest = $.ajax({
				type: 'post',
				url : 'massdeletepostgroup',
				data : $(".formtablecheckbox").serialize(),
				dataType: 'json',
				processData: false,
				xhrFields: {
					onprogress: function(e)
					{
						var progressResponse;
						var response = e.currentTarget.response;
						if(lastResponseLength === false)
						{
							progressResponse = response;
							lastResponseLength = response.length;
						}
						else
						{
							progressResponse = response.substring(lastResponseLength);
							lastResponseLength = response.length;
						}
						var parsedResponse = JSON.parse(progressResponse);
						tloader.fadeIn().html('<tr><td>'+parsedResponse.process+'</td></tr>');
					}
				}
			});

			ajaxRequest.done(function(data)
			{
				btn.prop('disabled',false);
				btn.val('Submit');
				$("input[type='hidden']").remove();
				tloader.fadeOut();
			});

			ajaxRequest.fail(function(error){
				var result = JSON.stringify(error, null, 4);
				btn.prop('disabled',false);
				btn.val('Submit');
				$("input[type='hidden']").remove();
				tloader.fadeOut();
			});


		})
	})
</script>