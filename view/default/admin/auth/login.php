<?php $this->view('admin/layouts/app'); ?>

<?php $this->section('title'); ?>
<title>Авторизация</title>
<?php $this->end(); ?>

<?php $this->section('body'); ?>
<body class="bg-login">
	<!--wrapper-->
	<div class="wrapper">
		<div class="section-authentication-signin d-flex align-items-center justify-content-center my-5 my-lg-0">
			<div class="container-fluid">
				<div class="row row-cols-1 row-cols-lg-2 row-cols-xl-3">
					<div class="col mx-auto">
						<div class="mb-4 text-center">
						</div>
						<div class="card">
							<div class="card-body">
								<div class="border p-4 rounded">
									<div class="form-body">
										<form class="row g-3" method="POST" action="<?= route('admin.auth') ?>">
											<div class="col-12">
												<label for="inputEmailAddress" class="form-label">Логин</label>
												<input name="login" value="<?= old('login') ?>" type="text" class="form-control" id="" placeholder="">
											</div>
                                            
                                            <div class="col-12">
												<label for="inputChoosePassword" class="form-label">Пароль</label>
												<div class="input-group" id="show_hide_password">
													<input type="password" name="password" class="form-control border-end-0" id="" value="" placeholder=""> <a href="javascript:;" class="input-group-text bg-transparent"><i class='bx bx-hide'></i></a>
												</div>
											</div>
											<div class="col-12">
												<div class="d-grid">
													<button type="submit" class="btn btn-primary"><i class="bx bxs-lock-open"></i>Войти</button>
												</div>
											</div>
										</form>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<!--end row-->
			</div>
		</div>
	</div>
	
	
</body>
<?php $this->end(); ?>

<?php $this->append('scripts'); ?>
<script>
		$(document).ready(function () {
			$("#show_hide_password a").on('click', function (event) {
				event.preventDefault();
				if ($('#show_hide_password input').attr("type") == "text") {
					$('#show_hide_password input').attr('type', 'password');
					$('#show_hide_password i').addClass("bx-hide");
					$('#show_hide_password i').removeClass("bx-show");
				} else if ($('#show_hide_password input').attr("type") == "password") {
					$('#show_hide_password input').attr('type', 'text');
					$('#show_hide_password i').removeClass("bx-hide");
					$('#show_hide_password i').addClass("bx-show");
				}
			});
		});
</script>
<?php $this->end(); ?>