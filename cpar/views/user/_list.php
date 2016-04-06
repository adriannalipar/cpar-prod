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
    <table id="list_tbl" class="table table-bordered table-striped">
      <thead>
        <tr>
          <th style="cursor: pointer;" class="user_sortable" data-sortby="first_name">
            Name
            <?php 
              if($sort_by == 'first_name') {
                if($sort == 'ASC') {
                  echo '<i class="glyphicon glyphicon-chevron-up pull-right"></i>';
                } else {
                  echo '<i class="glyphicon glyphicon-chevron-down pull-right"></i>';
                }
              }            
            ?>
          </th>
          <th>Email</th>
          <th>Team</th>
          <th>Role</th>          
          <th>Access Level</th>
          <th style="text-align: center;">User Status</th>
          <th style="width: 70px; text-align: center;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php 
          if(!empty($user_list)) {
            foreach ($user_list as $user): ?>
            <tr>
              <td><a href="/user/view/<?php echo $user['id']; ?>"><?php echo $user['full_name']; ?></a></td>
              <td><?php echo $user['email_address']; ?></td>
              <td><?php echo $user['team_name']; ?></td>
              <td><?php echo $user['role']; ?></td>
              <td><?php echo $user['access_level_name']; ?></td>
              <td>
                <?php 
                  if(intval($user['status']) == USER_STATUS_ACTIVE_FLAG) {
                    $badge_class = 'badge-success';
                  } else {
                    $badge_class = 'badge-danger';
                  }
                ?>
                <span class="badge <?php echo $badge_class; ?>"><?php echo $user['status_name']; ?></span>
              </td>
              <td>
                <input type="hidden" class="user_id_hdn" value="<?php echo $user['id']; ?>" />
                <a href="/user/edit/<?php echo $user['id']; ?>" class=""><i class="glyphicon glyphicon-pencil"></i></a>
                &nbsp;&nbsp;&nbsp;
                <a href="javascript:void(0)" class="user_delete_button"><i class="glyphicon glyphicon-trash"></i></a>
              </td>
            </tr>
        <?php endforeach; } else { ?>
          <tr>
            <td colspan="7" class="no_list_data">No rows to display.</td>
          </tr>
        <?php } ?>
      </tbody>
    </table>
  </div>   
</div>

<form id="user_delete_form" action="/user/delete" method="post" style="display: none;">
  <input type="hidden" id="user_id_hdn" name="id" value="" />
</form>