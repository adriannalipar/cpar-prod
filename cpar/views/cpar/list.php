<script src="/_js/cpar/list.js"></script>

<!-- CONTENT -->
<div id="content">
  <div class="container">

  	<!-- Error Messages -->
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

    <!-- Success Messages -->
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

    <!-- Search Form -->
    <div id="searchform">
      <div class="panel panel-default">
        <div class="panel-heading">
          <span class="glyphicon glyphicon-user"></span>&nbsp;
          <strong>Search CPAR Records</strong>
        </div>
        <div class="panel-body">
          <form id="cpar_search_form" class="basic_search_form form-inline" role="form">
            <input id="is_search" type="hidden" name="is_search" value="true"/>
            
            <!-- First search filters row -->
            <div class="row">
              <div class="col-md-12">

                <!-- CPAR No. -->
                <div class="form-group">
                  <label class="control-label" for="cpar_no">CPAR No.</label>
                  <input id="cpar_no" name="cpar_no" type="text" class="form-control input-md">
                </div>

                <!-- AR Type -->
                <div id="type_form_group" class="form-group">
                  <label class="control-label" for="ar_type">Type</label>
                  <select id="ar_type" name="ar_type" class="form-control input-md">
                    <option value="<?php echo DDVAL_GENERAL_ALL; ?>"><?php echo DDVAL_GENERAL_ALL; ?></option>
                    <option value="<?php echo CPAR_TYPE_C; ?>"><?php echo CPAR_TYPE_C_SHORT_NAME; ?></option>
                    <option value="<?php echo CPAR_TYPE_P; ?>"><?php echo CPAR_TYPE_P_SHORT_NAME; ?></option>
                  </select>
                </div>

                <!-- Date Created -->
                <div id="dcreated_form_group" class="form-group">
                  <label class="control-label" for="team">Date Filed</label>
                  <div class="input-group date">
                    <input id="dcreated" name="dcreated_from" type="text" class="search_dp datepicker form-control">
                    <span class="dp_ao input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
                  </div>
                  <span class="to_divider">to</span>
                  <div class="input-group date">
                    <input id="dcreated" name="dcreated_to" type="text" class="search_dp datepicker form-control input-sm">
                    <span class="dp_ao input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
                  </div>
                </div>

                <!-- Requestor -->
                <div id="requestor_form_group" class="form-group">
                  <label class="control-label" for="requestor">Requestor</label>
                  <input id="requestor" name="requestor" type="text" class="form-control input-md">
                </div>

              </div>
            </div>

            <br/>

            <!-- Second search filters row -->
            <div class="cpar_search_second_row row">
              <div class="col-md-12">

                <!-- Title -->
                <div id="title_form_group" class="form-group">
                  <label class="control-label" for="title">Title</label>
                  <input id="title" name="title" type="text" class="form-control input-md">
                </div>

                <!-- Date Due -->
                <div id="ddue_form_group" class="form-group">
                  <label class="control-label" for="team">Date Due</label>
                  <div class="input-group date">
                    <input id="ddue" name="ddue_from" type="text" class="search_dp datepicker form-control">
                    <span class="dp_ao input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
                  </div>
                  <span class="to_divider">to</span>
                  <div class="input-group date">
                    <input id="ddue" name="ddue_to" type="text" class="search_dp datepicker form-control input-sm">
                    <span class="dp_ao input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
                  </div>
                </div>

                <!-- Requestor -->
                <div id="addressee_form_group" class="form-group">
                  <label class="control-label" for="addressee">Addressee</label>
                  <input id="addressee" name="addressee" type="text" class="form-control input-md">
                </div>

              </div>
            </div>

            <br/>
            <!-- Search Buttons -->
            <div class="row">
              <div class="col-md-6">
                <a href="/cpar/create" class="btn btn-primary">
                  <i class="glyphicon glyphicon-plus"></i>&nbsp;Create New CPAR
                </a>
              </div>
              <div class="col-md-6">
                <div class="search_buttons_container pull-right">                  
                  <a id="cpar_search_btn" class="btn btn-primary">
                    <i class="glyphicon glyphicon-search"></i>&nbsp;Search
                  </a>
                  &nbsp;
                  <a href="/cpar/" class="btn btn-default">
                    <i class="glyphicon glyphicon-refresh"></i>&nbsp;Clear
                  </a>
                </div>
              </div>
            </div>

          </form>
        </div>
      </div>
    </div>

    <div id="cpar_list_wrapper">
      <?php require_once('partial/_list.php'); ?>
    </div>

  </div>
</div>