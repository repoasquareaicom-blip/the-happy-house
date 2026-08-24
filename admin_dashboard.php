<?php

session_start();
include 'config/data.php';
include 'class/stripe.php';
include 'class/log.php';
include 'class/subscription.php';
include 'objects/schooldata.php';

// dynamic css
include 'assets/css/pages/dynamicss.php';

$login_url = "admin_login.php";
if (
    !isset($_SESSION['admin_login_status']) ||
    $_SESSION['admin_login_status'] !== "true"
) {
	header("Location: $login_url");
    exit();
}
$_data = new Data();

$saved_content = "";
$check_curriculum_sql = "SELECT html_body FROM curriculum_content WHERE id = 1 LIMIT 1";
$curriculum_data = $_data->getData($check_curriculum_sql);

// Check if data exists, otherwise set a default
if (!empty($curriculum_data) && isset($curriculum_data[0]['html_body'])) {
    $saved_content = $curriculum_data[0]['html_body'];
} else {
    $saved_content = "<h3>Curriculum Learning Program</h3><p>Click here to start typing your content...</p>";
}

/* fetch current value safely */
$apple_link = $_data->getAppSetting('apple_store_link') ?? '';
$google_link  = $_data->getAppSetting('google_store_link') ?? '';
$stripe_price = $_data->getAppSetting('stripe_price_id') ?? '';
$classroom_on = $_data->getAppSetting('enable_classroom_setup') ?? '0';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>YD Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="assets/css/pages/dashboard.css">
    <link rel="stylesheet" href="assets/css/global.css">
    
    <link href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <!-- Bootstrap Tooltip Dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
     <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>


<!-- Favicons -->
<link rel="icon" href="./favicons/favicon.ico" sizes="any" />
<link rel="icon" href="./favicons/icon.svg" type="image/svg+xml" />
<link rel="apple-touch-icon" href="./favicons/apple-touch-icon.png" />
<link rel="manifest" href="./favicons/manifest.webmanifest" />

  <!--  Essential META Tags -->
	<meta property="og:title" content="The Happy House ">
	<meta property="og:type" content="website" />
	<meta property="og:image" content="https://thehappyhouse.au/dev/assets/images/The-Happy-House-Logo.svg">
	<meta property="og:url" content="https://thehappyhouse.au">



<style>
    .title {
        max-width:400px;
    }
    @media(max-width:840px)
    {
         .title {
        max-width:200px;
    }
    }
</style>

<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>

<style>
    /* Styling to make the editor fill the modal body */
    .note-editor.note-frame {
        border: none !important;
        height: calc(100vh - 120px) !important; /* Adjusted for header/footer */
        display: flex;
        flex-direction: column;
    }
    .note-editing-area { flex: 1; }
</style>

</head>
<body class="header_admin">
    <div class="dashboard-container">
        <!-- Header -->
	<section class="banner-background">
    <section class="banner">
        <img src="assets/images/banner-bg.svg" alt="Banner Background" class="banner-image" onclick="changeImage(this,'banner')" style="display: block;">
    </section>
   </section>
        <header class="responsive-header">
            <img src="assets/images/The-Happy-House-Logo.svg"  width="100%" height="100%" alt="Logo" class="logo">
            <div class="title"><img src="assets/images/YD-vector-logo.svg" width="100%" height="100%"></div>
            <div class="action-icons">
                <button class="icon-btn"><i class="fas fa-sign-out-alt" onclick="window.location.href='admin_logout.php';"></i></button>
            </div>
        </header>

        <!-- Content Section -->
        <div class="content-container">
            <!-- Right Section (Box First in Responsive) -->
            <main class="right-section">
                <div class="actions-row" id="actionRow">
                    <div class="action-box" id="activeSubcriptions">
                        <p>Schools with Active Subscriptions</p>
                        <i class="fas fa-tools action-icon"></i>
                    </div>
                    <div class="action-box" id="schoolLoginSupport">
                        <p>School Login Support</p>
                        <i class="fas fa-key action-icon"></i>
                    </div>
                    <div class="action-box" id="lockedSchools">
                        <p>Locked Schools</p>
                        <i class="fas fa-lock action-icon"></i>
                    </div>
                    <div class="action-box" id="gameResults">
                        <p>Wellbeging Results + Game Score Results</p>
                        <i class="fas fa-sync-alt action-icon"></i>
                    </div>
                    <!-- <div class="action-box" id="cancelledSubcriptions">
                        <p>Cancelled Subscriptions - Only Active</p>
                        <i class="fas fa-file-alt action-icon"></i>
                    </div> -->
                </div>
                <div id="dataTableDiv">
                    <div id="goBack">
                        <i class="fas fa-arrow-left"></i> Go Back
                    </div>
                    <div id="dataTitleText">
                    </div>
                    <div id="daTableRecords">
                    </div>
					<div id="divGameResults">
						<iframe id="iframeManageData" frameborder="0" style="width: 100%; height: 100%;"></iframe>
                    </div>
                </div>
            </main>

            <!-- Left Section (Second in Responsive) -->
            <aside class="left-section">
                <nav class="nav-links">
                    <a href="index.php" target="_blank" data-bs-toggle="modalss" data-bs-target="#landing_page_editorss"><i class="fas fa-edit"></i> Edit Landing Page</a>
					<a href="javascript:void(0);" 
                        class="btn btn-light" 
                        data-bs-toggle="modal" 
                        data-bs-target="#modal_helpful_tips">
                        <i class="fas fa-edit"></i> Edit Helpful Tips Page
                    </a>

                    <a href="javascript:void(0);" 
                        class="btn btn-light" 
                        data-bs-toggle="modal" 
                        data-bs-target="#modal_terms_of_use">
                        <i class="fas fa-edit"></i> Edit Terms of Use Page
                    </a>

                    <a href="javascript:void(0);" 
                        class="btn btn-light" 
                        data-bs-toggle="modal" 
                        data-bs-target="#modal_privacy_policy">
                        <i class="fas fa-edit"></i> Edit Privacy Policy Page
                    </a>
                    
                    <div class="nav-setting-item"
                        style="
                            display:flex;
                            align-items:center;
                            gap:8px;
                            padding:8px 14px;
                            width:100%;
                            box-sizing:border-box;
                        ">
                        <img src="assets/images/apple-icon.png" width="24" height="24">
                        <input type="url"
                            id="apple_store_link"
                            value="<?php echo htmlspecialchars($apple_link); ?>"
                            style="flex:1;height:30px;box-sizing:border-box;"
                            class="form-control"
                            placeholder="Apple App Store Link">
                        <button class="btn btn-sm" 
                                style="height:30px;min-width:52px;background-color: rgb(86, 121, 159);color:#fff"
                                        onclick="saveSetting('apple_store_link',
                                            document.getElementById('apple_store_link').value,
                                            this
                                        )">
                            Save
                        </button>
                    </div>
                    <div class="nav-setting-item"
                        style="
                            display:flex;
                            align-items:center;
                            gap:8px;
                            padding:8px 14px;
                            width:100%;
                            box-sizing:border-box;
                        ">
                        <img src="assets/images/google-icon.png" width="24" height="24">
                        <input type="url"
                            style="flex:1;height:30px;box-sizing:border-box;"
                            class="form-control"
                            placeholder="Google Play Store Store Link" id="google_store_link"
                            value="<?php echo htmlspecialchars($google_link, ENT_QUOTES); ?>">

                        <button class="btn btn-sm"
                                style="height:30px;min-width:52px;background-color: rgb(86, 121, 159);color:#fff"
                                onclick="saveSetting(
                                    'google_store_link',
                                    document.getElementById('google_store_link').value,
                                    this
                                )">
                            Save
                        </button>
                    </div>
                    
                    <div class="nav-setting-item"
                        style="
                            display:flex;
                            align-items:center;
                            gap:10px;
                            padding:8px 14px;
                            width:100%;
                            box-sizing:border-box;
                        ">

                        <!-- Toggle -->
                        <div class="form-check form-switch" style="margin:0;">
                            <input class="form-check-input"
                            type="checkbox"
                            id="classroom_toggle"
                            <?php echo ($classroom_on === '1') ? 'checked' : ''; ?>>
                        </div>

                        <!-- Text -->
                        <span style="
                            font-size:15px;
                            font-weight:500;
                            color:#000;
                            line-height:1.3;
                            flex:1;
                            white-space:normal;
                        ">
                            Enable Classroom Setup Button on Free App Start Page
                        </span>

                        <!-- Save Button -->
                        <button class="btn btn-sm"
                                style="height:30px;min-width:52px;background-color: rgb(86, 121, 159);color:#fff"
                                style="height:30px;min-width:52px;"
                                onclick="saveSetting(
                                    'enable_classroom_setup',
                                    document.getElementById('classroom_toggle').checked ? '1' : '0',
                                    this
                                )">
                            Save
                        </button>

                    </div>
                    <div style="padding: 15px 14px 5px 14px; border-top: 1px solid #eee; margin-top: 10px;">
                        <h2 style="margin: 0;font-size:24px; color: #666;  letter-spacing: 0.5px;">
                            Manage Stripe Prices
                        </h2>
                    </div>

                    
                        <a href="javascript:void(0);" 
                        class="btn btn-light" 
                        data-bs-toggle="modal" 
                        data-bs-target="#priceConfigModal">
                        <i class="fas fa-edit"></i> Edit Stripe Prices
                    </a>
                    <div style="padding: 15px 14px 5px 14px; border-top: 1px solid #eee; margin-top: 10px;">
                        <h2 style="margin: 0;font-size:24px; color: #666;  letter-spacing: 0.5px;">
                            Curriculum Learning Program
                        </h2>
                    </div>

                    
                        <a href="javascript:void(0);" 
                        class="btn btn-light" 
                        data-bs-toggle="modal" 
                        data-bs-target="#curriculumEditorModal">
                        <i class="fas fa-edit"></i> Edit Curriculum Content
                    </a>
                    

                    
                </nav>
            </aside>
        </div>

        <!-- Footer -->
        <footer class="footer">
            <p>&copy; 2025 The Happy House. All rights reserved.</p>
        </footer>
    </div>
    <div class="modal fade" id="priceConfigModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
                <div class="modal-header" style="background: #f8f9fa; border-bottom: 1px solid #eee;">
                    <h5 class="modal-title" style="color: #004666; font-weight: 600;">
                        <i class="fab fa-stripe me-2" style="color: #6772e5;"></i>Product Configuration
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" style="max-height: 70vh; overflow-y: auto; padding: 20px;">
                    <form id="priceUpdateForm">
                        <?php
                        $products = $_data->getData("SELECT * FROM products_master WHERE status = 1");
                        foreach($products as $prod): 
                        ?>
                        <div class="product-settings-card mb-4" 
                            style="background: #ffffff; border: 1px solid #e0e6ed; border-radius: 10px; padding: 15px; position: relative;">
                            
                            <span class="badge" style="position: absolute; top: -10px; right: 15px; background: #eef2f7; color: #56799f; border: 1px solid #d1d9e0;">
                                Key: <?php echo $prod['product_key']; ?>
                            </span>

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">Display Name</label>
                                <input type="text" class="form-control form-control-sm" 
                                    id="name_<?php echo $prod['product_key']; ?>" 
                                    value="<?php echo htmlspecialchars($prod['display_name']); ?>"
                                    style="border-color: #d1d9e0; font-weight: 500;">
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">Stripe Price ID</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text" style="background: #f8f9fa;"><i class="fas fa-tag"></i></span>
                                    <input type="text" class="form-control" 
                                        id="price_id_<?php echo $prod['product_key']; ?>" 
                                        value="<?php echo htmlspecialchars($prod['stripe_price_id']); ?>">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-7">
                                    <label class="form-label small fw-bold text-muted">Price (Yearly)</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">AUD</span>
                                        <input type="number" step="0.01" class="form-control" 
                                            id="amount_<?php echo $prod['product_key']; ?>" 
                                            value="<?php echo $prod['price_amount']; ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </form>
                </div>
                <div class="modal-footer" style="background: #f8f9fa; border-top: 1px solid #eee;">
                    <button type="button" class="btn btn-link btn-sm text-muted" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-sm" 
                            style="background: #56799f; color: #fff; padding: 8px 20px; border-radius: 6px;" 
                            onclick="updateAllPrices(this)">
                        <span class="btn-text">Update Products</span>
                        <div class="spinner-border spinner-border-sm d-none" id="saveSpinner" role="status"></div>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="modal_helpful_tips" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title"><i class="fas fa-lightbulb"></i> Edit Helpful Tips</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <textarea id="editor_helpful_tips" class="summernote-inst">
                        <?php echo file_exists('helpful-tips.html') ? file_get_contents('helpful-tips.html') : ''; ?>
                    </textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="savePageContent('helpful_tips', 'helpful-tips.html', this)">Save Helpful Tips</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal_terms_of_use" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title"><i class="fas fa-file-contract"></i> Edit Terms of Use</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <textarea id="editor_terms_of_use" class="summernote-inst">
                        <?php echo file_exists('terms-of-use.html') ? file_get_contents('terms-of-use.html') : ''; ?>
                    </textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="savePageContent('terms_of_use', 'terms-of-use.html', this)">Save Terms of Use</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal_privacy_policy" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title"><i class="fas fa-user-shield"></i> Edit Privacy Policy</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <textarea id="editor_privacy_policy" class="summernote-inst">
                        <?php echo file_exists('privacy-policy.html') ? file_get_contents('privacy-policy.html') : ''; ?>
                    </textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="savePageContent('privacy_policy', 'privacy-policy.html', this)">Save Privacy Policy</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="curriculumEditorModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title"><i class="fas fa-chalkboard-teacher"></i> Edit Curriculum Learning Program landing page</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <!-- We use the same class 'summernote-inst' so your existing CSS applies -->
                    <textarea id="curriculum_editor" class="summernote-inst">
                        <?php 
                            // Pulling the $saved_content we fetched from the DB earlier
                            echo isset($saved_content) ? $saved_content : ''; 
                        ?>
                    </textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <!-- Custom Save function for DB logic -->
                    <button type="button" class="btn btn-primary" onclick="saveCurriculumContent(this)">
                        <i class="fas fa-save"></i> Save Curriculum
                    </button>
                </div>
            </div>
        </div>
    </div>
	
	<!-- The Modal -->
<div class="modal" id="landing_page_editor">
  <div class="modal-dialog modal-fullscreen">
    <div class="modal-content">

      <!-- Modal Header -->
      <div class="modal-header">
        <h4 class="modal-title">Landing Page</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
		
      <!-- Modal body -->
      <div class="modal-body">
		<div class="row" style="padding:10px;">
		    <div class="col-lg-2">
			<div id="banner_bg_img" class="col-lg-12" style="margin-bottom: 5px; border: solid 3px #dedede;border-radius: 6px;   background-color: rgba(0, 0, 0, 0.2);">
				<h4 class="img_header">Banner section Image</h4>
				<div class="profile_img" style="background-color: rgba(0,0,0,0.3); text-align:center;  padding: 5px; color: #fff; max-width: 100%; overflow: hidden; border-radius: 6px;">
					<img id="banner-bg" src="assets/images/banner-bg.png?<?php echo rand(10,100);?>" style="max-width: 160px; height: 100px; margin-bottom: 5px;"/>
					
					<input id="btn_browse" class="btn btn-primary" type="button" value="Change" onclick="browse_img('banner-bg.png')"/>
				</div>
			</div>
			<div id="section1_bg_img" class="col-lg-12" style="margin-bottom: 5px; border: solid 3px #dedede;border-radius: 6px;   background-color: rgba(0, 0, 0, 0.2);">
				<h4 class="img_header">Product section Image</h4>
				<div class="profile_img" style="background-color: rgba(0,0,0,0.3); text-align:center; padding: 5px; color: #fff; max-width: 100%; overflow: hidden; border-radius: 6px;">
					<img id="product-bg" src="assets/images/product-bg.png?<?php echo rand(10,100);?>" style="max-width: 160px;  height: 100px; margin-bottom: 5px;"/>
					
					<input id="product-bg" class="btn btn-primary" type="button" value="Change" onclick="browse_img('product-bg.png')"/>
				</div>
			</div>
			<div id="section2_bg_img" class="col-lg-12" style="margin-bottom: 5px; border: solid 3px #dedede;border-radius: 6px;   background-color: rgba(0, 0, 0, 0.2);">
				<h4 class="img_header">About section Image</h4>
				<div class="profile_img" style="background-color: rgba(0,0,0,0.3); text-align:center; padding: 5px; color: #fff; max-width: 100%; overflow: hidden; border-radius: 6px;">
					<img id="about-bg" src="assets/images/about-bg.png?<?php echo rand(10,100);?>" style="max-width: 160px;  height: 100px; margin-bottom: 5px;"/>
					
					<input id="btn_browse" class="btn btn-primary" type="button" value="Change" onclick="browse_img('about-bg.png')"/>
				</div>
			</div>
			<div id="section2_bg_img" class="col-lg-12" style="margin-bottom: 5px; border: solid 3px #dedede;border-radius: 6px;   background-color: rgba(0, 0, 0, 0.2);">
				<h4 class="img_header">Video section</h4>
				<div class="profile_img" style="background-color: rgba(0,0,0,0.3); text-align:center; padding: 5px; color: #fff; max-width: 100%; overflow: hidden; border-radius: 6px;">
					
					<video  id="video-content" height="100px" src="assets/images/video-content.mp4?<?php echo rand(10,100);?>"></video>					
					
					<input id="btn_browse" class="btn btn-primary" type="button" value="Change" onclick="browse_img('video-content.mp4')"/>
				</div>
			</div>
		    </div>
		    <div class="col-lg-10">
		
			<textarea id="editor_area">
				<?php
					$_data = new Data();
					$_query = "select * from content_master where page ='landing_page'";
					$c_record = $_data->getData($_query);
					
					foreach($c_record  as $_record) {
						echo $_record['content'];
					}
				
				?>
			</textarea></div>
			</div>
      </div>

      <!-- Modal footer -->
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" onclick="refresh_content();" style="background-color: #26be26;">Refresh Content</button>
		<button type="button" class="btn btn-primary" onclick="update_content();">Update</button>
        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
      </div>

    </div>
  </div>
</div>

<div style="display:none;">
	<form id="ajaxupload" action="ajaxupload.php" method="post" >
		<input id="uploadImage" type="file" accept="image/png, video/mp4" name="image" />
		<input id="f_name" type="text" name="f_name" />
		<input id="uploadSubmit" class="btn btn-success" type="submit" value="Upload">
	</form>
</div>

<div id="lodingDiv" style="display: none;position: fixed; top:0px; left: 0px; right:0px; bottom:0px; background-color:rgba(255,255,255,0.7); text-align: center; padding-top: 15%; z-index: 9999999;">
<!--<div style="margin-left:auto;margin-right:auto;text-align:center;"><img src="assets/images/Loading_2.gif"/></div>-->

<div style="margin-left:auto; margin-right:auto;" class="loader"></div>
<div>Please wait.......</div>
</div>

<div class="modal fade" id="pdf_uploader" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">PDF Manager</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="pdfUploadForm">
                    <div class="mb-4 text-center">
                        <a id="current_pdf_link" href="uploads/tips.pdf" target="_blank" style="color: #d3988d; text-decoration: none; font-size: 14px;">
                            <i class="fas fa-eye"></i> View Current Document
                        </a>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <select class="form-select" name="doc_type" id="doc_type" onchange="updatePreviewLink()">
                            <option value="tips">Helpful Tips</option>
                            <option value="terms">Terms Of Use</option>
                            <option value="privacy">Privacy Policy</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Choose File</label>
                        <input type="file" name="pdf_file" id="pdf_file" class="form-control" accept=".pdf" required>
                    </div>

                    <div class="progress mb-3" style="display:none; height: 20px;border-radius:10px">
                        <div id="progressBar" class="progress-bar" role="progressbar" style="width: 0%; background-color: #d3988d;"></div>
                    </div>

                    <div id="uploadStatus" class="mt-2 text-center"></div>

                    <button type="submit" class="btn btn-upload-pdf w-100">
                        Upload Now
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="setSchoolPasswordModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title"><i class="fas fa-key"></i> Reset School Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="setSchoolPasswordForm">
                <div class="modal-body">
                    <input type="hidden" id="passwordSchoolId" name="school_id">
                    <div class="mb-3">
                        <label class="form-label">School</label>
                        <div id="passwordSchoolName" class="fw-bold"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <div id="passwordSchoolEmail" class="text-muted"></div>
                    </div>
                    <div class="mb-3">
                        <label for="newSchoolPassword" class="form-label">New Password</label>
                        <input type="password" id="newSchoolPassword" name="new_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="confirmSchoolPassword" class="form-label">Confirm New Password</label>
                        <input type="password" id="confirmSchoolPassword" name="confirm_password" class="form-control" required>
                    </div>
                    <div id="passwordModalError" class="text-danger small"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Reset Password</button>
                </div>
            </form>
        </div>
    </div>
</div>
    
</body>
<script>
  var schoolLoginSupportKeyword = '';
  var lockedSchoolsKeyword = '';

  document.getElementById('activeSubcriptions').onclick = function() {
    document.getElementById('actionRow').style.display = 'none';
	document.getElementById('divGameResults').style.display = 'none';
    document.getElementById('dataTableDiv').style.display = 'block';
	document.getElementById('daTableRecords').style.display = 'block';
    document.getElementById('dataTitleText').innerText = "Active Subscriptions";
    getSubscriptions("active");
  };
  document.getElementById('schoolLoginSupport').onclick = function() {
    schoolLoginSupportKeyword = '';
    document.getElementById('actionRow').style.display = 'none';
	document.getElementById('divGameResults').style.display = 'none';
    document.getElementById('dataTableDiv').style.display = 'block';
	document.getElementById('daTableRecords').style.display = 'block';
    document.getElementById('dataTitleText').style.display = 'block';
    document.getElementById('dataTitleText').innerText = "School Login Support";
    getSchoolLoginSupport();
  };
  document.getElementById('lockedSchools').onclick = function() {
    lockedSchoolsKeyword = '';
    document.getElementById('actionRow').style.display = 'none';
	document.getElementById('divGameResults').style.display = 'none';
    document.getElementById('dataTableDiv').style.display = 'block';
	document.getElementById('daTableRecords').style.display = 'block';
    document.getElementById('dataTitleText').style.display = 'block';
    document.getElementById('dataTitleText').innerText = "Locked Schools";
    getLockedSchools();
  };
  document.getElementById('gameResults').onclick = function() {
    document.getElementById('actionRow').style.display = 'none';
    document.getElementById('dataTableDiv').style.display = 'block';
	document.getElementById('daTableRecords').style.display = 'none';
    document.getElementById('dataTitleText').style.display = 'none';
	document.getElementById('divGameResults').style.display = 'block';
    $('#iframeManageData').attr('src', 'reports/report_admin.php');
	const iframe = $('#iframeManageData');
	iframe.css('height', `calc(100vh - 100px)`);
  };
  document.getElementById('goBack').onclick = function() { 
    document.getElementById('actionRow').style.display = 'block';
    document.getElementById('dataTableDiv').style.display = 'none';
  };
  
</script>
<script>

function refresh_content(){
    //alert(12366);
     var bgrefresh = document.getElementsByTagName('iframe')[0].contentWindow.document.getElementsByClassName('bgrefresh');

		for(var i=0; i<bgrefresh.length;i++){
			var t = bgrefresh[i].getAttribute('data-img-type');
			var img = bgrefresh[i].getAttribute('data-bg');
			if(t=="bg"){
				bgrefresh[i].style.backgroundImage = "url('"+ img+"?"+Math.random() + "')";
			}
			else if(t=="src"){
				bgrefresh[i].setAttribute('src', img+"?"+Math.random());
			}
		}
}

function getSubscriptions(type) {
    $('#daTableRecords').html("");
    $.ajax({
    url: 'AJAX.php',
    type: 'POST',
    data: {
        method: type=='active'?'getActiveSubscription':'getCancelledSubscription'
    },
    dataType: 'json',
    success: function(response) {
        
       var tableHtml = 
    '<table id="dataTable" class="table table-bordered table-striped">' +
    '<thead>' +
    '<tr>' +
    '<th style="display: none;">S.No</th>' +
    '<th>School Details</th>' +
    '<th>Wellbeing Games Status</th>' +
    '<th>Curriculum Status</th>' +
    '</tr>' +
    '</thead>' +
    '<tbody>';

var n = 1;
response.forEach(function(row) {
    
    // --- 1. Games Status Logic ---
    var gamesBadge = '';
    var gamesDates = (row.subscription_start || '') + ' - ' + (row.subscription_end || '');
    
    if (!row.subscription_id || row.subscription_id == '' || row.subscription_id == '0') {
        // Never had a subscription
        gamesBadge = '<span class="badge badge-secondary">Not Subscribed</span>';
        gamesDates = '<small class="text-muted">No History</small>';
    } else if (row.cancel_at && row.cancel_at !== null) {
        // Active but will cancel at end of period
        gamesBadge = `<span class="badge badge-warning text-dark" title="Ends: ${row.cancel_at}">Pending Cancel</span>`;
    } else if (row.status !== 'active') {
        // Had a subscription, but it is now inactive/expired
        gamesBadge = '<span class="badge badge-danger">Cancelled / Expired</span>';
    } else {
        // Fully active
        gamesBadge = '<span class="badge badge-success">Active</span>';
    }

    // --- 2. Curriculum Status Logic ---
    var currBadge = '';
    var currDates = (row.curriculum_start || '') + ' - ' + (row.curriculum_end || '');

    if (!row.curriculum_sub_id || row.curriculum_sub_id == '' || row.curriculum_sub_id == '0') {
        // Never had a curriculum subscription
        currBadge = '<span class="badge badge-secondary">Not Subscribed</span>';
        currDates = '<small class="text-muted">No History</small>';
    } else if (row.curriculum_cancel_at && row.curriculum_cancel_at !== null) {
        // Active but will cancel
        currBadge = `<span class="badge badge-warning text-dark" title="Ends: ${row.curriculum_cancel_at}">Pending Cancel</span>`;
    } else if (row.curriculum_status !== 'active') {
        // Expired or Cancelled
        currBadge = '<span class="badge badge-danger">Cancelled / Expired</span>';
    } else {
        // Fully active
        currBadge = '<span class="badge badge-success">Active</span>';
    }

    tableHtml += 
        '<tr>' +
        '<td style="display: none;">' + n + '</td>' +
        '<td>' +
            '<strong>' + escapeHtml(row.school_name) + '</strong><br>' +
            '<small class="text-muted">' + escapeHtml(row.school_admin_email) + '</small>' +
        '</td>' +
        '<td>' + gamesBadge + '<br><small>' + gamesDates + '</small></td>' +
        '<td>' + currBadge + '<br><small>' + currDates + '</small></td>' +
        '</tr>';
    n++;
});

tableHtml += '</tbody></table>';
$('#daTableRecords').html(tableHtml);
$('#dataTable').DataTable();
    },
    error: function(xhr, status, error) {
        console.error('AJAX Error:', {
            status: xhr.status,
            statusText: xhr.statusText,
            responseText: xhr.responseText
        });
    }
});
 
}

function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>"']/g, function(match) {
        return {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'}[match];
    });
}

function escapeJs(value) {
    return String(value ?? '').replace(/\\/g, '\\\\').replace(/'/g, "\\'").replace(/\r?\n/g, ' ');
}

function renderSchoolSearchControls(inputId, searchFunctionName, clearFunctionName, keyword) {
    return '<div class="mb-3 d-flex align-items-center flex-wrap" style="gap: 8px;">' +
        '<label for="' + inputId + '" class="mb-0 fw-bold">Search School / Email:</label>' +
        '<input type="text" id="' + inputId + '" class="form-control" style="max-width: 360px;" value="' + escapeHtml(keyword) + '" onkeydown="handleSchoolSearchEnter(event, \'' + searchFunctionName + '\')">' +
        '<button type="button" class="btn btn-primary" onclick="' + searchFunctionName + '()">Search</button>' +
        '<button type="button" class="btn btn-secondary" onclick="' + clearFunctionName + '()">Clear</button>' +
    '</div>';
}

function handleSchoolSearchEnter(event, searchFunctionName) {
    if (event.key === 'Enter') {
        event.preventDefault();
        window[searchFunctionName]();
    }
}

function searchLockedSchools() {
    lockedSchoolsKeyword = $('#lockedSchoolsSearchKeyword').val().trim();
    getLockedSchools();
}

function clearLockedSchoolsSearch() {
    lockedSchoolsKeyword = '';
    getLockedSchools();
}

function searchSchoolLoginSupport() {
    schoolLoginSupportKeyword = $('#schoolLoginSupportSearchKeyword').val().trim();
    getSchoolLoginSupport();
}

function clearSchoolLoginSupportSearch() {
    schoolLoginSupportKeyword = '';
    getSchoolLoginSupport();
}

function getLockedSchools() {
    $('#daTableRecords').html("");
    $.ajax({
        url: 'AJAX.php',
        type: 'POST',
        data: { method: 'getLockedSchools', keyword: lockedSchoolsKeyword },
        dataType: 'json',
        success: function(response) {
            if (response.error) {
                showAlert(response.error);
                return;
            }

            var tableHtml = renderSchoolSearchControls('lockedSchoolsSearchKeyword', 'searchLockedSchools', 'clearLockedSchoolsSearch', lockedSchoolsKeyword) +
                '<table id="dataTable" class="table table-bordered table-striped">' +
                '<thead><tr>' +
                '<th>School Details</th>' +
                '<th>Failed Attempts</th>' +
                '<th>Locked Until</th>' +
                '<th>Actions</th>' +
                '</tr></thead><tbody>';

            response.forEach(function(row) {
                tableHtml += '<tr>' +
                    '<td><strong>' + escapeHtml(row.school_name) + '</strong><br><small class="text-muted">' + escapeHtml(row.school_admin_email) + '</small></td>' +
                    '<td>' + escapeHtml(row.failed_login_attempts) + '</td>' +
                    '<td>' + escapeHtml(row.login_locked_until) + '</td>' +
                    '<td>' +
                        '<button type="button" class="btn btn-sm btn-primary" onclick="openSetPasswordModal(' + row.id + ', \'' + escapeJs(row.school_name) + '\', \'' + escapeJs(row.school_admin_email) + '\')">Reset Password</button>' +
                    '</td>' +
                '</tr>';
            });

            tableHtml += '</tbody></table>';
            $('#daTableRecords').html(tableHtml);
            $('#dataTable').DataTable();
        },
        error: function() {
            showAlert('Could not load locked schools.');
        }
    });
}

function getSchoolLoginSupport() {
    $('#daTableRecords').html("");
    $.ajax({
        url: 'AJAX.php',
        type: 'POST',
        data: { method: 'getSchoolLoginSupport', keyword: schoolLoginSupportKeyword },
        dataType: 'json',
        success: function(response) {
            if (response.error) {
                showAlert(response.error);
                return;
            }

            var tableHtml = renderSchoolSearchControls('schoolLoginSupportSearchKeyword', 'searchSchoolLoginSupport', 'clearSchoolLoginSupportSearch', schoolLoginSupportKeyword) +
                '<table id="dataTable" class="table table-bordered table-striped">' +
                '<thead><tr>' +
                '<th>School Details</th>' +
                '<th>Login Status</th>' +
                '<th>Failed Attempts</th>' +
                '<th>Actions</th>' +
                '</tr></thead><tbody>';

            response.forEach(function(row) {
                tableHtml += '<tr>' +
                    '<td><strong>' + escapeHtml(row.school_name) + '</strong><br><small class="text-muted">' + escapeHtml(row.school_admin_email) + '</small></td>' +
                    '<td><span class="badge ' + (row.login_status === 'Locked' ? 'badge-danger' : 'badge-success') + '">' + escapeHtml(row.login_status) + '</span></td>' +
                    '<td>' + escapeHtml(row.failed_login_attempts) + '</td>' +
                    '<td><button type="button" class="btn btn-sm btn-primary" onclick="openSetPasswordModal(' + row.id + ', \'' + escapeJs(row.school_name) + '\', \'' + escapeJs(row.school_admin_email) + '\')">Reset Password</button></td>' +
                '</tr>';
            });

            tableHtml += '</tbody></table>';
            $('#daTableRecords').html(tableHtml);
            $('#dataTable').DataTable();
        },
        error: function() {
            showAlert('Could not load school login support.');
        }
    });
}

function openSetPasswordModal(schoolId, schoolName, schoolEmail) {
    $('#passwordSchoolId').val(schoolId);
    $('#passwordSchoolName').text(schoolName);
    $('#passwordSchoolEmail').text(schoolEmail);
    $('#newSchoolPassword').val('');
    $('#confirmSchoolPassword').val('');
    $('#passwordModalError').text('');
    bootstrap.Modal.getOrCreateInstance(document.getElementById('setSchoolPasswordModal')).show();
}

$('#setSchoolPasswordForm').on('submit', function(e) {
    e.preventDefault();
    $('#passwordModalError').text('');

    $.ajax({
        url: 'admin_set_school_password.php',
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                $('#newSchoolPassword').val('');
                $('#confirmSchoolPassword').val('');
                bootstrap.Modal.getInstance(document.getElementById('setSchoolPasswordModal')).hide();
                showAlert('Password reset successfully. The school can now log in with the new password.', 'success');
                if ($('#dataTitleText').text() === 'Locked Schools') {
                    getLockedSchools();
                } else if ($('#dataTitleText').text() === 'School Login Support') {
                    getSchoolLoginSupport();
                }
            } else {
                $('#passwordModalError').text(response.message || 'Unable to update password.');
            }
        },
        error: function() {
            $('#passwordModalError').text('Unable to update password.');
        }
    });
});
</script>

<script src="assets/ckeditor/ckeditor.js"></script>
<script>
    var editorInstance;
    var dataSet = false;
	$(document).ready(function(){
		
		load_editor();
		$("#uploadImage").change(function (){
			$("#uploadSubmit").click();
		});
	});
	
	function addScriptInCkEditor(){
		try{
			var frame = document.getElementsByTagName('iframe')[0];
			
			var scriptObj = document.createElement("script");
					scriptObj.type = "text/javascript";
					scriptObj.id = scriptId;
					scriptObj.setAttribute("src", "https://thehappyhouse.au/assets/js/refresh.js");
					frame.contentWindow.document.head.appendChild(scriptObj);
		}
		catch(e){
			console.log(e);
		}
				
	}
	
	
	function load_editor(){
	    
	    
CKEDITOR.config.syntaxhighlight_showColumns = true;

CKEDITOR.config.syntaxhighlight_noWrap = true;

CKEDITOR.config.syntaxhighlight_firstLine = 0; // default 0

CKEDITOR.config.syntaxhighlight_lang = 'xml', 'xhtml', 'xslt', 'html'; // default null

CKEDITOR.config.syntaxhighlight_code = 'html'; // default ''
	    
		CKEDITOR.config.height = '80vh';
		CKEDITOR.config.width = 'auto';
		CKEDITOR.replace( 'editor_area',{
			on: {
				instanceReady: function() {
					editorInstance = this;
					if(!dataSet){
					    dataSet = true;
					    this.setData(atob($("#editor_area").val()));
					    setTimeout(function(){refresh_content();}, 300);
					}
					this.document.appendStyleSheet( 'https://thehappyhouse.au/dev/assets/css/pages/index.css' );
					 this.on( 'mode', function() {
						 if(this.mode == 'wysiwyg' ){
								this.document.appendStyleSheet( 'https://thehappyhouse.au/dev/assets/css/pages/index.css' );
								addScriptInCkEditor();
						 }
						console.log( this.name + ' works in ' + this.mode + ' mode' );
					 });
				}
			}
		});
	}
	
	function browse_img(val){
		$("#f_name").val(val);
		$("#uploadImage").click();
	}
	
	$("#ajaxupload").on('submit',(function(e) {
	  e.preventDefault();
	  $.ajax({
			 url: "ajaxupload.php",
	   type: "POST",
	   data:  new FormData(this),
	   contentType: false,
			 cache: false,
	   processData:false,
	   beforeSend : function()
	   {
		//$("#preview").fadeOut();
		//$("#err").fadeOut();
		
		$("#lodingDiv").css('display', 'block');
	   },
	   success: function(data)
		  {
			
			var img = $("#f_name").val();
			var img_id = img.replace('.png', '');
			img_id = img_id.replace('.mp4', '');
		
			
			
			$("#lodingDiv").css('display', 'none');
			
			//editorInstance.setData($("#editor_area").val());
			setTimeout(function(){
			    	$("#"+img_id).attr("src", "assets/images/"+img + "?"+Math.random());
			    refresh_content();
			    
			},600);
			
			//load_editor();
			alert('Image updated');
		  },
		 error: function(e) 
		  {
			$("#lodingDiv").css('display', 'none');
			//alert(e);
		  }          
		});
		
	 }));
	 
	 
	 
	 
	 function update_content(){
		$("#lodingDiv").css('display', 'block');
		$.ajax({
			url: 'api/controller.php',
			type: 'POST',
			data: {
				request_type: "save_content_landing_Page",
				content: btoa(editorInstance.getData())
				
			},
			dataType: 'json',
			success: function(response) {
				$("#lodingDiv").css('display', 'none');
			   alert('Content Updated');
			},
			error: function(xhr, status, error) {
				$("#lodingDiv").css('display', 'none');
				alert(error);
			}
		});
		 
	 }
	
</script>
<script>
function updatePreviewLink() {
    const docType = document.getElementById('doc_type').value;
    const previewLink = document.getElementById('current_pdf_link');
    
    // Updates the link dynamically based on dropdown
    // Adding a timestamp prevents the browser from showing a cached old version
    const timestamp = new Date().getTime();
    previewLink.href = `uploads/${docType}.pdf?v=${timestamp}`;
}


// Get the modal element
var myModalEl = document.getElementById('landing_page_editor');

myModalEl.addEventListener('show.bs.modal', function (event) {
    // 1. Reset the entire form
    var form = document.getElementById('pdfUploadForm');
    form.reset();

    // 2. Hide the progress bar and set it back to 0%
    var progressContainer = document.querySelector('.progress');
    var progressBar = document.getElementById('progressBar');
    progressContainer.style.display = 'none';
    progressBar.style.width = '0%';
    progressBar.innerHTML = '0%';

    // 3. Clear status messages (Success/Error)
    var status = document.getElementById('uploadStatus');
    status.innerHTML = '';

    // 4. Reset the "View Current" link to the default (Tips)
    updatePreviewLink();
});


document.getElementById('pdfUploadForm').onsubmit = function(e) {
    e.preventDefault();
    
    let formData = new FormData(this);
    let xhr = new XMLHttpRequest();
    let progressBar = document.getElementById('progressBar');
    let status = document.getElementById('uploadStatus');

    // Show progress bar
    document.querySelector('.progress').style.display = 'flex';

    xhr.open("POST", "upload_handler.php", true);

    // Track Progress
    xhr.upload.onprogress = function(e) {
        if (e.lengthComputable) {
            let percent = Math.round((e.loaded / e.total) * 100);
            progressBar.style.width = percent + '%';
            progressBar.innerHTML = percent + '%';
        }
    };

    xhr.onload = function() {
        if (xhr.status === 200) {
            status.innerHTML = '<span class="text-success">Upload Successful!</span>';
            updatePreviewLink();
			setTimeout(function() {
            progressBar.style.width = '0%';
            document.querySelector('.progress').style.display = 'none';
            form.reset();
        }, 2000);
        } else {
            status.innerHTML = '<span class="text-danger">Upload Failed.</span>';
        }
    };

    xhr.send(formData);
};



</script>

<style>
	.cke_notification{
		display: none;
	}
		::-webkit-scrollbar {
	  width: 10px;
	}

	/* Track */
	::-webkit-scrollbar-track {
	  background: #f1f1f1; 
	}
	 
	/* Handle */
	::-webkit-scrollbar-thumb {
	  background: #888; 
	}

	/* Handle on hover */
	::-webkit-scrollbar-thumb:hover {
	  background: #555; 
	}
	.img_header{
		width: 100%;
		padding:5px;
		background-color: #d2d2d2;
		color: #000;
		font-size:12px;
		text-align: center;
	}
	
	


.loader {
  width: 50px;
  aspect-ratio: 1;
  color: #f03355;
  --_c:no-repeat radial-gradient(farthest-side,currentColor 92%,#0000);
  background: 
    var(--_c) 50% 0   /12px 12px,
    var(--_c) 50% 100%/12px 12px,
    var(--_c) 100% 50%/12px 12px,
    var(--_c) 0    50%/12px 12px,
    var(--_c) 50%  50%/12px 12px,
    conic-gradient(from 90deg at 4px 4px,#0000 90deg,currentColor 0)
    -4px -4px/calc(50% + 2px) calc(50% + 2px);
  animation: l8 1s infinite linear;
}
@keyframes l8 {to{transform: rotate(.5turn)}}

/* Specific Submit Button in Modal */
.btn-upload-pdf {
    background-color: #d3988d !important;
    border-color: #d3988d !important;
    color: white !important;
    font-weight: 600;
    padding: 12px;
    border-radius: 6px;
    transition: background-color 0.3s ease;
}

.btn-upload-pdf:hover {
    background-color: #c4877c !important; /* Slightly darker on hover */
    border-color: #c4877c !important;
}

/* Matching Progress Bar */
.progress-bar {
    background-color: #d3988d !important;
}

/* Optional: Subtle focus color for inputs */
.form-select:focus, .form-control:focus {
    border-color: #d3988d;
    box-shadow: 0 0 0 0.25rem rgba(211, 152, 141, 0.25);
}
.btn-custom-save {
    background-color: rgb(86, 121, 159);
    
    color: #fff;
}
</style>
<script>
function saveSetting(key, value, btn) {
    btn.disabled = true;
    btn.innerText = 'Saving...';

    fetch('admin_save_app_settings.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: new URLSearchParams({
            key: key,
            value: value
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            btn.innerText = 'Saved';
            setTimeout(() => {
                btn.innerText = 'Save';
                btn.disabled = false;
            }, 1200);
        } else {
            alert('Save failed');
            btn.disabled = false;
            btn.innerText = 'Save';
        }
    })
    .catch(() => {
        alert('Server error');
        btn.disabled = false;
        btn.innerText = 'Save';
    });
}

function savePageContent(editorId, fileName, btnElement) {
    // If using TinyMCE or CKEditor, get content via their API:
    // const content = tinymce.get('editor_' + editorId).getContent();
    const content = document.getElementById('editor_' + editorId).value; 
    
    const originalText = btnElement.innerHTML;
    btnElement.disabled = true;
    btnElement.innerHTML = "Saving...";

    fetch('save_pages.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `filename=${encodeURIComponent(fileName)}&content=${encodeURIComponent(content)}`
    })
    .then(response => response.text())
    .then(res => {
        alert("Content updated successfully!");
        btnElement.disabled = false;
        btnElement.innerHTML = originalText;
    })
    .catch(err => {
        console.error(err);
        btnElement.disabled = false;
        btnElement.innerHTML = "Error";
    });
}

$(document).ready(function() {
    // Initialize all editors
    $('#editor_helpful_tips, #editor_terms_of_use, #editor_privacy_policy').summernote({
        placeholder: 'Write your content here...',
        tabsize: 2,
        height: '100%',
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'underline', 'clear']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['table', ['table']],
            ['insert', ['link', 'picture', 'video']],
            ['view', ['fullscreen', 'codeview', 'help']]
        ]
    });
});

function savePageContent(editorId, fileName, btnElement) {
    // Get content from Summernote
    const content = $('#editor_' + editorId).summernote('code');
    
    const originalText = btnElement.innerHTML;
    btnElement.disabled = true;
    btnElement.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

    $.ajax({
        url: 'save_pages.php',
        method: 'POST',
        data: {
            filename: fileName,
            content: content
        },
        success: function(response) {
            alert("File '" + fileName + "' saved successfully!");
            btnElement.disabled = false;
            btnElement.innerHTML = originalText;
        },
        error: function() {
            alert("Error saving file. Check permissions.");
            btnElement.disabled = false;
            btnElement.innerHTML = originalText;
        }
    });
}
$(document).ready(function() {
    // Initialize all editors with the class 'summernote-inst'
    $('.summernote-inst').summernote({
        placeholder: 'Start typing your content...',
        tabsize: 2,
        height: 'calc(100vh - 150px)', // Auto-adjust height for fullscreen
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'underline', 'clear', 'italic']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['table', ['table']],
            ['insert', ['link', 'picture']],
            ['view', ['fullscreen', 'codeview']]
        ]
    });
});

function savePageContent(editorId, fileName, btnElement) {
    // Get content specifically from the targeted editor
    const content = $('#editor_' + editorId).summernote('code');
    
    const originalText = btnElement.innerHTML;
    btnElement.disabled = true;
    btnElement.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

    $.ajax({
        url: 'save_pages.php',
        method: 'POST',
        data: {
            filename: fileName,
            content: content
        },
        success: function(response) {
            // Using a nicer notification or a simple alert
            alert(fileName + " has been updated successfully!");
            btnElement.disabled = false;
            btnElement.innerHTML = originalText;
        },
        error: function() {
            alert("Error saving " + fileName + ". Please check folder permissions.");
            btnElement.disabled = false;
            btnElement.innerHTML = originalText;
        }
    });
}
</script>

<script>
$(document).ready(function() {
    // When the Curriculum modal is shown, initialize Summernote
    $('#curriculumEditorModal').on('shown.bs.modal', function () {
        $('#curriculum_editor').summernote({
            placeholder: 'Write your curriculum content here...',
            tabsize: 2,
            height: '70vh', // Uses 70% of the screen height
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'underline', 'clear']],
                ['fontname', ['fontname']],
                ['fontsize', ['fontsize']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['alignment', ['justifyLeft', 'justifyCenter', 'justifyRight', 'justifyFull']],
                ['table', ['table']],
                ['insert', ['link', 'picture', 'video']],
                ['view', ['fullscreen', 'codeview', 'help']]
            ],
            // This fix handles the extra line spacing (p margin)
            callbacks: {
                onInit: function() {
                    $('.note-editable p').css('margin-bottom', '0px');
                    $('.note-editable').css('line-height', '1.5');
                }
            }
        });
    });

    // Destroy editor when modal closes to keep the page fast
    $('#curriculumEditorModal').on('hidden.bs.modal', function () {
        $('#curriculum_editor').summernote('destroy');
    });
});

function saveCurriculumContent(btn) {
    var htmlData = $('#curriculum_editor').summernote('code');
    var originalHtml = btn.innerHTML;
    
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

    $.ajax({
        url: 'save_curriculum.php',
        method: 'POST',
        data: { content: htmlData },
        success: function(response) {
            if(response.trim() === "success") {
                btn.innerHTML = '<i class="fas fa-check"></i> Saved!';
                setTimeout(function() {
                    $('#curriculumEditorModal').modal('hide');
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                }, 1000);
            } else {
                alert('Error: ' + response);
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            }
        }
    });
}

function updateAllPrices(btn) {
    const loader = btn.querySelector('.spinner-border');
    const btnText = btn.querySelector('.btn-text');
    
    // 1. Show Spinner, Hide Text, Disable Button
    btn.disabled = true;
    btnText.style.display = 'none';
    loader.classList.remove('d-none'); // or .remove('hidden')

    const data = {
        wellbeing_games: {
            display_name: document.getElementById('name_wellbeing_games').value,
            price_id: document.getElementById('price_id_wellbeing_games').value,
            amount: document.getElementById('amount_wellbeing_games').value
        },
        curriculum: {
            display_name: document.getElementById('name_curriculum').value,
            price_id: document.getElementById('price_id_curriculum').value,
            amount: document.getElementById('amount_curriculum').value
        }
    };

    $.ajax({
        url: 'update_product_prices.php',
        method: 'POST',
        data: { prices: JSON.stringify(data) },
        success: function(response) {
            btn.disabled = false;
            btnText.style.display = 'inline-block';
            loader.classList.add('d-none');
            showAlert("Settings updated successfully!", "success");
            //setTimeout(() => location.reload(), 1000);
        },
        error: function() {
            btn.disabled = false;
            btnText.style.display = 'inline-block';
            loader.classList.add('d-none');
            showAlert("Failed to save changes.", "error");
            // Reset button on error so user can try again
            
        }
    });
}
let alertTimeout;
function showAlert(message, type = 'error') {
        const container = document.getElementById('globalAlertContainer');
        const msgBox = document.getElementById('globalAlertMessage');
        const iconBox = document.getElementById('globalAlertIcon');
        const content = container.querySelector('.global-alert-content');

        // 1. Reset Classes
        content.classList.remove('alert-success', 'alert-error');
        
        // 2. Set Type & Icon
        if (type === 'success') {
            content.classList.add('alert-success');
            iconBox.innerHTML = '<i class="fas fa-check-circle"></i>';
        } else {
            content.classList.add('alert-error');
            iconBox.innerHTML = '<i class="fas fa-exclamation-circle"></i>';
        }

        // 3. Set Message
        msgBox.innerText = message;

        // 4. Show Alert
        container.classList.remove('global-alert-hidden');

        // 5. Auto-hide after 5 seconds
        clearTimeout(alertTimeout);
        alertTimeout = setTimeout(() => {
            closeGlobalAlert();
        }, 50000);
    } function closeGlobalAlert() {
        document.getElementById('globalAlertContainer').classList.add('global-alert-hidden');
    }
</script>
<div id="globalAlertContainer" class="global-alert-hidden">
    <div class="global-alert-content">
        <div id="globalAlertIcon"></div>
        <div id="globalAlertMessage"></div>
        <button type="button" class="global-alert-close" onclick="closeGlobalAlert()">&times;</button>
    </div>
</div>
<style>
#globalAlertContainer {
    position: fixed;
    top: 20px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 9999; /* Higher than Bootstrap Modals */
    width: 90%;
    max-width: 400px;
    transition: all 0.3s ease-in-out;
}

.global-alert-hidden {
    top: -100px !important; /* Hides it above the screen */
    opacity: 0;
    pointer-events: none;
}

.global-alert-content {
    background: white;
    padding: 15px 40px 15px 20px;
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    display: flex;
    align-items: center;
    gap: 12px;
    position: relative;
    border-left: 6px solid #ccc;
}

.global-alert-close {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    font-size: 22px;
    cursor: pointer;
    color: #999;
}

/* Success State */
.alert-success { border-left-color: #28a745 !important; background-color: #f2fff5 !important; color: #155724 !important; }
/* Error State */
.alert-error { border-left-color: #dc3545 !important; background-color: #fff2f2 !important; color: #721c24 !important; }
</style>


</html>
