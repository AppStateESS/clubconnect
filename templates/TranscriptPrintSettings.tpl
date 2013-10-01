<form method="POST" action="{post_uri}">
  <h1>Printer Settings for {name} <small>{revision}</small></h1>
  <div class="row">
    <div class="col-lg-3 col-md-3 col-sm-3">
      <h3>General</h3>
      <div class="form-group">
        <label for="std_font_size">Font Size</label>
        <input type="number" class="form-control" name="std_font_size" value="{std_font_size}">
      </div>
      <div class="form-group">
        <label for="cell_height">Cell Height</label>
        <input type="number" class="form-control" name="cell_height" value="{cell_height}">
      </div>
      <div class="form-group">
        <label for="body_y_offset">Body Y-Offset</label>
        <input type="number" class="form-control" name="body_y_offset" value="{body_y_offset}">
      </div>
    </div>
    <div class="col-lg-3 col-md-3 col-sm-3">
      <h3>Header</h3>
      <div class="form-group">
        <label for="start_y">Start Y</label>
        <input type="number" class="form-control" name="start_y" value="{start_y}">
      </div>
      <div class="form-group">
        <label for="start_x">Start X</label>
        <input type="number" class="form-control" name="start_x" value="{start_x}">
      </div>
      <div class="form-group">
        <label for="name_width">Name Width</label>
        <input type="number" class="form-control" name="name_width" value="{name_width}">
      </div>
      <div class="form-group">
        <label for="sid_x_offset">SID X-Offset</label>
        <input type="number" class="form-control" name="sid_x_offset" value="{sid_x_offset}">
      </div>
      <div class="form-group">
        <label for="sid_width">SID Width</label>
        <input type="number" class="form-control" name="sid_width" value="{sid_width}">
      </div>
    </div>
    <div class="col-lg-3 col-md-3 col-sm-3">
      <h3>Footer</h3>
      <div class="form-group">
        <label for="foot_x">Footer X</label>
        <input type="number" class="form-control" name="foot_x" value="{foot_x}">
      </div>
      <div class="form-group">
        <label for="foot_y">Footer Y</label>
        <input type="number" class="form-control" name="foot_y" value="{foot_y}">
      </div>
      <div class="form-group">
        <label for="date_width">Date Width</label>
        <input type="number" class="form-control" name="date_width" value="{date_width}">
      </div>
      <div class="form-group">
        <label for="pn_x_offset">Page Number X-Offset</label>
        <input type="number" class="form-control" name="pn_x_offset" value="{pn_x_offset}">
      </div>
      <div class="form-group">
        <label for="pn_width">Page Number Width</label>
        <input type="number" class="form-control" name="pn_width" value="{pn_width}">
      </div>
      <div class="form-group">
        <label for="of_x_offset">Page Total X-Offset</label>
        <input type="number" class="form-control" name="of_x_offset" value="{of_x_offset}">
      </div>
      <div class="form-group">
        <label for="of_width">Page Total Width</label>
        <input type="number" class="form-control" name="of_width" value="{of_width}">
      </div>
    </div>
  </div>
  <input type="submit" class="btn btn-primary btn-block" value="Save Settings">
</form>
