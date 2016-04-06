<!-- tabs -->
</br>
<ul id="cpar_tabs" class="nav nav-tabs" role="tablist">
  <li <?php if(strcmp($tab, CPAR_TAB_ALL) == 0) { echo "class='active'"; } ?>>
    <a data-stage="All" class="cpar_tab" href="#">All</a>
  </li>
  <li <?php if($tab == CPAR_TAB_STAGE_1) { echo "class='active'"; } ?>>
    <a data-stage="1" class="cpar_tab" href="#">Stage 1</a>
  </li>
  <li <?php if($tab == CPAR_TAB_STAGE_2) { echo "class='active'"; } ?>>
    <a data-stage="2" class="cpar_tab" href="#">Stage 2</a>
  </li>
  <li <?php if($tab == CPAR_TAB_STAGE_3) { echo "class='active'"; } ?>>
    <a data-stage="3" class="cpar_tab" href="#">Stage 3</a>
  </li>
  <li <?php if($tab == CPAR_TAB_STAGE_4) { echo "class='active'"; } ?>>
    <a data-stage="4" class="cpar_tab" href="#">Stage 4</a>
  </li>
  <li <?php if($tab == CPAR_TAB_STAGE_5) { echo "class='active'"; } ?>>
    <a data-stage="5" class="cpar_tab" href="#">Stage 5</a>
  </li>
</ul>

<div class="row pagination_top">
  <div class="col-md-3">
  </div>
  <div class="col-md-6">
    <div class="results_per_page_container">
      <table>
        <tr>
          <td>Results per page</td>
          <td>
            <select id="pgtn_results_per_page" class="form-control">
              <option <?php if($rpp == 10) { echo "selected='selected'"; } ?>>10</option>
              <option <?php if($rpp == 25) { echo "selected='selected'"; } ?>>25</option>
              <option <?php if($rpp == 50) { echo "selected='selected'"; } ?>>50</option>
              <option <?php if(strcmp(strval($rpp), RPP_ALL) == 0) { echo "selected='selected'"; } ?>><?php echo RPP_ALL; ?></option>
            </select>
          </td>
        </tr>
      </table>
    </div>        
  </div>
  <div class="col-md-3" style="text-align: right;">
    <?php if(strcmp($rpp, RPP_ALL) != 0) { ?>
      <ul class="pagination">
        <?php
          $prev = $min_page - PAGES_PER_SET; 
          if($prev < 1) {
            echo "<li class='disabled'><a href='#' onclick='return false;'>Prev</a></li>";
          } else{
            echo "<li><a href='#' onclick='return false;' data-page='$prev'>Prev</a></li>";
          }
        ?>
        <?php for($i = $min_page ; $i <= $max_page ; $i++) { 
            if($i == intval($pn)) {
              echo "<li class='active'><a href='#' class='btn-primary-lighter' data-page='$i' onclick='return false;'>$i</a></li>";
            } else {
              echo "<li><a href='#' data-page='$i' onclick='return false;'>$i</a></li>";
            }
          }
        ?>
        <?php
          $next = $max_page + 1; 
          if($next > $pages) {
            echo "<li class='disabled'><a href='#' onclick='return false;'>Next</a></li>";
          } else{
            echo "<li><a href='#' onclick='return false;' data-page='$next'>Next</a></li>";
          }
        ?>
      </ul>
    <?php } ?>
  </div>
</div>

<div style="position: relative;">
  <div class="pagination_info">
    <?php
      $from = (($pn - 1) * $rpp) + 1;
      $to = $from + $nor - 1;
    ?>
    <span class="pagination_info_content">Showing <?php echo $from . ' - ' . $to ?> of <?php echo $count; ?> results</span>
  </div>
  <div id="list_tbl_container">
    <input type="hidden" id="sort" value="<?php echo $sort; ?>" />    
    <input type="hidden" id="sort_by" value="<?php echo $sort_by; ?>" />
    <table id="list_tbl" class="cpar_tbl table table-bordered table-striped">
      <thead>
        <tr>
          <th style="cursor: pointer;" class="center_mid cpar_col_id cpar_sortable" data-sortby="cpar_main.id">
            CPAR No.
            <?php 
              if($sort_by == 'cpar_main.id') {
                if($sort == 'ASC') {
                  echo '<i class="glyphicon glyphicon-chevron-up pull-right"></i>';
                } else {
                  echo '<i class="glyphicon glyphicon-chevron-down pull-right"></i>';
                }
              }
            ?>
          </th>
          <th style="cursor: pointer;" class="center_mid cpar_col_date cpar_sortable" data-sortby="cpar_main.created_date">
            Date Filed
            <?php 
              if($sort_by == 'cpar_main.date_filed') {
                if($sort == 'ASC') {
                  echo '<i class="glyphicon glyphicon-chevron-up pull-right"></i>';
                } else {
                  echo '<i class="glyphicon glyphicon-chevron-down pull-right"></i>';
                }
              }
            ?>
          </th>
          <th style="cursor: pointer;" class="center_mid cpar_col_name cpar_sortable" data-sortby="req.first_name">
            Originator
            <?php 
              if($sort_by == 'req.first_name') {
                if($sort == 'ASC') {
                  echo '<i class="glyphicon glyphicon-chevron-up pull-right"></i>';
                } else {
                  echo '<i class="glyphicon glyphicon-chevron-down pull-right"></i>';
                }
              }
            ?>
          </th>
          <th style="cursor: pointer;" class="center_mid cpar_col_name cpar_sortable" data-sortby="adr.first_name">
            Addressee
            <?php 
              if($sort_by == 'adr.first_name') {
                if($sort == 'ASC') {
                  echo '<i class="glyphicon glyphicon-chevron-up pull-right"></i>';
                } else {
                  echo '<i class="glyphicon glyphicon-chevron-down pull-right"></i>';
                }
              }
            ?>
          </th>        
          <th style="cursor: pointer;" class="center_mid cpar_sortable" data-sortby="raaro.name">
          	Filed as a Result of
		  		<?php 
	              if($sort_by == 'raaro.name') {
	                if($sort == 'ASC') {
	                  echo '<i class="glyphicon glyphicon-chevron-up pull-right"></i>';
	                } else {
	                  echo '<i class="glyphicon glyphicon-chevron-down pull-right"></i>';
	                }
	              }
	            ?>          </th>
          <th style="cursor: pointer;" class="center_mid cpar_col_title cpar_sortable" data-sortby="cpar_main.title">
          	Title
            <?php 
              if($sort_by == 'cpar_main.title') {
                if($sort == 'ASC') {
                  echo '<i class="glyphicon glyphicon-chevron-up pull-right"></i>';
                } else {
                  echo '<i class="glyphicon glyphicon-chevron-down pull-right"></i>';
                }
              }
            ?>
          </th>

          <?php if(strcmp($tab, CPAR_TAB_ALL) == 0) { ?>
            <th style="cursor: pointer;" class="center_mid cpar_col_stage cpar_sortable" data-sortby="cpar_main.status">
            	Stage
	            <?php 
	              if($sort_by == 'cpar_main.status') {
	                if($sort == 'ASC') {
	                  echo '<i class="glyphicon glyphicon-chevron-up pull-right"></i>';
	                } else {
	                  echo '<i class="glyphicon glyphicon-chevron-down pull-right"></i>';
	                }
	              }
	            ?>
            </th>
          <?php } ?>

          <th class="center_mid">Status</th>

          <?php if($tab != CPAR_TAB_STAGE_1 && $tab != CPAR_TAB_STAGE_5) { ?>
            <th style="cursor: pointer;" class="center_mid cpar_col_date cpar_sortable" data-sortby="cpar_main.date_due">
              <?php 
                if(strcmp($tab, CPAR_TAB_ALL) == 0) { 
                  echo 'Due Date'; 
                } else if((int)$tab == CPAR_TAB_STAGE_2) {
                  echo 'Next Due Date';
                } else if((int)$tab == CPAR_TAB_STAGE_3) {
                  echo 'Completion Date';
                } else if((int)$tab == CPAR_TAB_STAGE_4) {
                  echo 'IMS Due Date';
                } 
              ?>
              <?php 
                if($sort_by == 'cpar_main.date_due') {
                  if($sort == 'ASC') {
                    echo '<i class="glyphicon glyphicon-chevron-up pull-right"></i>';
                  } else {
                    echo '<i class="glyphicon glyphicon-chevron-down pull-right"></i>';
                  }
                }
              ?>
            </th>
          <?php } ?>

          <?php if((int)$tab == CPAR_TAB_STAGE_3) { ?>
          <th style="cursor: pointer;" class="center_mid cpar_col_date cpar_sortable" data-sortby="cpar_main.ff_up_date">
            Follow-up Date
            <?php 
              if($sort_by == 'cpar_main.ff_up_date') {
                if($sort == 'ASC') {
                  echo '<i class="glyphicon glyphicon-chevron-up pull-right"></i>';
                } else {
                  echo '<i class="glyphicon glyphicon-chevron-down pull-right"></i>';
                }
              }
            ?>
          </th>
          <?php } ?>

          <?php if((int)$tab == CPAR_TAB_STAGE_5) { ?>
          <th style="cursor: pointer;" class="center_mid cpar_col_date cpar_sortable" data-sortby="cpar_main.closure_date">
            Closure Date
            <?php 
              if($sort_by == 'cpar_main.closure_date') {
                if($sort == 'ASC') {
                  echo '<i class="glyphicon glyphicon-chevron-up pull-right"></i>';
                } else {
                  echo '<i class="glyphicon glyphicon-chevron-down pull-right"></i>';
                }
              }
            ?>
          </th>
          <?php } ?>
        </tr>
      </thead>
      <tbody>
        <?php 
          if(!empty($cpar_list)) {
            foreach ($cpar_list as $cpar):
              $item_url = '#';
              if($cpar['status'] == CPAR_TAB_STAGE_1) {
                if(strcmp($cpar['sub_status'], CPAR_MINI_STATUS_DRAFT) == 0 || 
                   strcmp($cpar['sub_status'], CPAR_MINI_STATUS_FOR_IMS_REVIEW) == 0 ||
                   strcmp($cpar['sub_status'], CPAR_MINI_STATUS_PUSHED_BACK) == 0) {
                  $item_url = '/cpar/edit/' . $cpar['id'];
                } else if(strcmp($cpar['sub_status'], CPAR_MINI_STATUS_CLOSED) == 0) {
                  $item_url = '/cpar/view/' . $cpar['id'];
                }
              } else if($cpar['status'] == CPAR_TAB_STAGE_2) {
                if(strcmp($cpar['sub_status'], CPAR_MINI_STATUS_S2_2A1) == 0 || strcmp($cpar['sub_status'], CPAR_MINI_STATUS_S2_2A2) == 0) {
                  $item_url = '/cpar_s2/edit/' . $cpar['id'];
                } else if(strcmp($cpar['sub_status'], CPAR_MINI_STATUS_S2_2B) == 0) {
                  $item_url = '/cpar_s2/review/' . $cpar['id'];
                }
              } else if($cpar['status'] == CPAR_TAB_STAGE_3) {
                if(strcmp($cpar['sub_status'], CPAR_MINI_STATUS_S3_3A) == 0) {
                  $item_url = '/cpar_s3/review/' . $cpar['id'];
                } else if(strcmp($cpar['sub_status'], CPAR_MINI_STATUS_S3_3B) == 0 || 
                  strcmp($cpar['sub_status'], CPAR_MINI_STATUS_S3_3B2) == 0) {
                  $item_url = '/cpar_s3/edit/' . $cpar['id'];
                }
              } else if($cpar['status'] == CPAR_TAB_STAGE_4) {
                if(strcmp($cpar['sub_status'], CPAR_MINI_STATUS_S4_4A) == 0 || strcmp($cpar['sub_status'], CPAR_MINI_STATUS_S4_4A2) == 0) {
                  $item_url = '/cpar_s4/review/' . $cpar['id'];
                } else if(strcmp($cpar['sub_status'], CPAR_MINI_STATUS_S4_4B) == 0) {
                  $item_url = '/cpar_s4/review/' . $cpar['id'];
                }
              } else if($cpar['status'] == CPAR_TAB_STAGE_5) {
                if(strcmp($cpar['sub_status'], CPAR_MINI_STATUS_S5_5A) == 0) {
                  $item_url = '/cpar_s5/view/' . $cpar['id'];
                }
              }
        ?>
            <tr>
              <td><a href="<?php echo $item_url; ?>"><?php echo $cpar['id']; ?></a></td>
              
              <td><?php echo $cpar['date_filed_formatted']; ?></td>
              <td><?php echo $cpar['req_name']; ?></td>
              <td><?php echo $cpar['adr_name']; ?></td>
              <td><?php echo $cpar['raaro_name']; ?></td>
              <td><?php echo $cpar['title']; ?></td>

              <?php if(strcmp($tab, CPAR_TAB_ALL) == 0) { ?>
                <td><?php echo $cpar['status_name']; ?></td>
              <?php } ?>

              <td><?php echo getSubStatusName($cpar['sub_status']); ?></td>

              <?php if($tab != CPAR_TAB_STAGE_1 && $tab != CPAR_TAB_STAGE_5) { ?>
                <td><?php echo $cpar['date_due_formatted']; ?></td>
              <?php } ?>

              <?php if($tab == CPAR_TAB_STAGE_3) { ?>
                <td><?php echo $cpar['ff_up_date_formatted']; ?></td>
              <?php } ?>

              <?php if($tab == CPAR_TAB_STAGE_5) { ?>
                <td><?php echo $cpar['closure_date_formatted']; ?></td>
              <?php } ?>
            </tr>
        <?php endforeach; } else { 
          if(strcmp($tab, CPAR_TAB_ALL) == 0) {
            $colspan = 9;
          } else if($tab == CPAR_TAB_STAGE_1) {
            $colspan = 7;
          } else if($tab == CPAR_TAB_STAGE_2) {
            $colspan = 8;
          } else if($tab == CPAR_TAB_STAGE_3) {
            $colspan = 9;
          } else if($tab == CPAR_TAB_STAGE_4) {
            $colspan = 8;
          }
          //TODO: get colspans of other Stages
          else {
            $colspan = 8;
          } 
        ?>
          <tr>
            <td colspan="<?php echo $colspan; ?>" class="no_list_data">No rows to display.</td>
          </tr>
        <?php } ?>
      </tbody>
    </table>
  </div>
</div>

<?php if(!($cpar_list == null || empty($cpar_list))) { ?> 
  <div id="export_btn_container">
    <br/>
    <a id="export_btn" class="btn btn-primary pull-right">
      <i class="glyphicon glyphicon-export"></i>&nbsp;Export to CSV
    </a>
  </div>
<?php } ?>