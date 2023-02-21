<!doctype html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1" />
    
    <?php $this->show('title'); ?>
    <title>Admin Panel</title>
    <?php $this->end(); ?>
    
    <link rel="stylesheet" href="<?= asset('/assets/css/all.css') ?>" />
    
	<link rel="stylesheet" href="<?= asset('/assets/css/style.css?t='.time()) ?>" />
    
    <script>window.indexAppData = <?= json_encode(['csrfToken' => csrf_token()]) ?></script>
    
    <?php $this->block('styles'); ?>
</head>

<?php $this->show('body'); ?>
<body style="overflow: auto;">
	<!--wrapper-->
	<div class="wrapper">
		<!--sidebar wrapper -->
		<div class="sidebar-wrapper" data-simplebar="true">
			<div class="sidebar-header">
				<div>
					<h4 class="logo-text">Admin Panel</h4>
				</div>
				<div class="toggle-icon ms-auto"><i class='bx bx-arrow-to-left'></i>
				</div>
			</div>
			<!--navigation-->
			<ul class="metismenu" id="menu">
				<li>
					<a href="#">
						Товары
					</a>
				</li>
			</ul>
			<!--end navigation-->
		</div>
		<!--end sidebar wrapper -->
		<!--start header -->
		<header>
			<div class="topbar d-flex align-items-center">
				<nav class="navbar navbar-expand" style="width: 100%;">
					<div class="mobile-toggle-menu"><i class='bx bx-menu'></i>
					</div>
					<div style="margin-left: 6px;margin-right: 12px;">
						<div>
							<h5><?php $this->block('title_header'); ?></h5>
						</div>
					</div>
                    
                    <div style="width: 100%;white-space: nowrap;">
                    <div style="">
                    </div>
                    </div>
                    
					<div class="top-menu ms-auto" >
						<ul class="navbar-nav align-items-center">
							<li class="nav-item dropdown dropdown-large">
								<a class="nav-link dropdown-toggle dropdown-toggle-nocaret" href="<?= route('admin.logout') ?>">
								<h6 style="">Выйти</h6>
                                </a>
								<div class="dropdown-menu dropdown-menu-end">
									<div class="row row-cols-3 g-3 p-3">
										
									</div>
								</div>
							</li>
							<li class="nav-item dropdown dropdown-large" style="display: none!important;">
								<a class="nav-link dropdown-toggle dropdown-toggle-nocaret position-relative" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
								</a>
								<div class="dropdown-menu dropdown-menu-end">
									<a href="javascript:;">
									</a>
									<div class="header-notifications-list">
										
									</div>
									<a href="javascript:;">
									</a>
								</div>
							</li>
							<li class="nav-item dropdown dropdown-large" style="display: none!important;">
								<a class="nav-link dropdown-toggle dropdown-toggle-nocaret position-relative" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
								</a>
								<div class="dropdown-menu dropdown-menu-end">
									<a href="javascript:;">
									</a>
									<div class="header-message-list">
									</div>
									<a href="javascript:;">
									</a>
								</div>
							</li>
						</ul>
					</div>
				</nav>
			</div>
		</header>
		<!--end header -->
		<!--start page wrapper -->
		<div class="page-wrapper">
			<div class="page-content">
				<?php $this->block('page_content'); ?> 
			</div>
		</div>
		<!--end page wrapper -->
        
        <footer class="page-footer">
			<p class="mb-0">&nbsp;</p>
		</footer>
	</div>
	<!--end wrapper-->
    
   
</body>
<?php $this->end(); ?>
    <!--
	<script src="assets/js/bootstrap.bundle.min.js"></script>
	<script src="assets/js/jquery.min.js"></script>
	<script src="assets/js/simplebar.min.js"></script>
	<script src="assets/js/metisMenu.min.js"></script>
	<script src="assets/js/perfect-scrollbar.js"></script>
	<script src="assets/js/app.js"></script>
    <script type="text/javascript" src="/assets/js/chosen.jquery.min.js"></script>
    -->
    
	<script src="<?= asset('/assets/js/all.js') ?>"></script>
    
	<script src="<?= asset('/assets/js/scripts.js?t='.time()) ?>"></script>
    
    <?php $this->block('scripts'); ?>
</html>