<script src="/_js/user/list.js"></script>

<!-- CONTENT -->
<div id="content">
  <div class="container">

    <div class="row">
      <div class="col-md-12">
        <div class="error_container alert alert-danger">
          <button type="button" class="close custom_alert_hide">
            <span aria-hidden="true">&times;</span>
            <span class="sr-only">Close</span>
          </button>
          <div class="error_content">
            <?php
              $errors = $this->session->userdata('search_page_errors');
              if(!($errors == null || empty($errors))) {
                echo '<input type="hidden" id="hasErrors_hdn" value="true" />';
                foreach ($errors as $error) {
                  echo "<i class='glyphicon glyphicon-exclamation-sign'></i>&nbsp;$error<br/>";
                }
                $this->session->unset_userdata('search_page_errors');
              }
            ?>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-md-12">
        <div class="success_msgs_container alert alert-success">
          <button type="button" class="close custom_alert_hide">
            <span aria-hidden="true">&times;</span>
            <span class="sr-only">Close</span>
          </button>
          <div class="success_msg_content">
            <?php
              $success_msgs = $this->session->userdata('search_page_success_msgs');
              if(!($success_msgs == null || empty($success_msgs))) {
                echo '<input type="hidden" id="hasSuccessMsgs_hdn" value="true" />';
                foreach ($success_msgs as $success_msg) {
                  echo "<i class='glyphicon glyphicon-exclamation-check'></i>&nbsp;$success_msg<br/>";
                }
                $this->session->unset_userdata('search_page_success_msgs');
              }
            ?>
          </div>
        </div>
      </div>
    </div>

    <div id="searchform">
      <div class="panel panel-default">
        <div class="panel-heading">
          <span class="glyphicon glyphicon-user"></span>&nbsp;
          <strong>Search Users</strong>
        </div>
        <div class="panel-body">
          <form id="user_search_form" class="basic_search_form form-inline" role="form">
            <input type="hidden" name="is_search" value="true"/>
            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <label class="control-label" for="name">Name</label>
                  <input id="name" name="name" type="text" class="form-control input-md">
                </div>
                <div id="role_form_group" class="form-group">
                  <label class="control-label" for="role">Role</label>
                  <select id="role" name="role" class="form-control input-md">
                    <option>All</option>
                    <option value="<?php echo DDVAL_ROLE_IMS_ONLY; ?>">IMS</option>
                    <option value="<?php echo DDVAL_ROLE_MR_ONLY; ?>">MR</option>
                    <option value="<?php echo DDVAL_ROLE_NORMAL_USER; ?>">Normal User</option>
                  </select>
                </div>
                <div id="team_form_group" class="form-group">
                  <label class="control-label" for="team">Team</label>
                  <select id="team" name="team" class="form-control input-md">
                    <option>All</option>
                    <?php foreach($teams as $team): ?>
                      <option value="<?php echo $team['id']; ?>"><?php echo $team['name']; ?></option>
                    <?php endforeach; ?>>
                  </select>
                </div>
              </div>
            </div>
            <br />
            <div class="row">
              <div class="col-md-12">
                <div id="email_form_group" class="form-group">
                  <label class="control-label" for="email">Email</label>
                  <input id="email" name="email" type="text" class="form-control input-md">
                </div>
                <div id="access_level_form_group" class="form-group">
                  <label class="control-label" for="access_level">Access Level</label>
                  <select id="access_level" name="access_level" class="form-control input-md">
                    <option>All</option>
                    <option value="<?php echo DDVAL_ACCESSLVL_ADMIN; ?>">Administrator</option>
                    <option value="<?php echo DDVAL_ACCESSLVL_USER; ?>">User</option>
                  </select>
                </div>
                <div id="status_form_group" class="form-group">
                  <label class="control-label" for="status">Status</label>
                  <select id="status" name="status" class="form-control input-md">
                    <option>All</option>
                    <option value="<?php echo DDVAL_STATUS_ACTIVE; ?>">Active</option>
                    <option value="<?php echo DDVAL_STATUS_INACTIVE; ?>">Inactive</option>
                  </select>
                </div>
              </div>
            </div>
            <br />
            <div class="row">
              <div class="col-md-12">
                <div class="search_buttons_container pull-right">                  
                  <a id="user_search_btn" href="#" class="btn btn-primary">
                    <i class="glyphicon glyphicon-search"></i>&nbsp;Search
                  </a>
                  &nbsp;
                  <a href="/user/" class="btn btn-default">
                    <i class="glyphicon glyphicon-refresh"></i>&nbsp;Clear
                  </a>
                </div>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>

    <div class="add_record_container row">
      <div class="col-md-12">
        <a href="/user/create" class="btn btn-primary">
          <i class="glyphicon glyphicon-plus"></i>&nbsp;Add New User
        </a>
      </div>
    </div>

    <div id="user_list_wrapper">
      <?php require_once('_list.php'); ?>
    </div>
  </div>
</div>
<!-- END OF CONTENT -->

<!-- MODALS -->
<div id="modals_container"> 
  <div class="modal fade" id="confirm_delete_modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog medium_dialog">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h4 class="modal-title">Confirm Delete</h4>
        </div>
        <div class="modal-body">
          Are you sure you want to delete this record?
        </div>
        <div class="modal-footer">
          <a id="true_delete_btn" class="btn btn-primary">
            &nbsp;Ok
          </a>
          <a data-dismiss="modal" aria-hidden="true" class="btn btn-default">
            Cancel
          </a>
        </div>
      </div> 
    </div>
  </div>
</div>