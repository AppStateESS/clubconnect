<script type="text/javascript">
  CKEDITOR_BASEPATH = '{JAVASCRIPT_BASE}/AngularFrontend/ckeditor/';
</script>
<script src="{JAVASCRIPT_BASE}/AngularFrontend/ckeditor/ckeditor.js"></script>
<script src="{JAVASCRIPT_BASE}/AngularFrontend/angular.min.js"></script>
<script src="{JAVASCRIPT_BASE}/AngularFrontend/scripts/5dfaa861.plugins.js"></script>
<script src="{JAVASCRIPT_BASE}/AngularFrontend/scripts/a2183bc5.modules.js"></script>
<script src="{JAVASCRIPT_BASE}/AngularFrontend/scripts/375e685a.scripts.js"></script>
<script type="text/javascript">
  angular.module('ClubConnectApp')
  .config(function(registrationApiProvider) {
      registrationApiProvider.setUri({REGISTRATION});
  })
  .config(function(officerRequestApiProvider) {
      officerRequestApiProvider.setUri({OFFICER_REQUEST});
  })
  .config(function(userApiProvider) {
      userApiProvider.setUri({USER});
  })
  .config(function(rolesProvider) {
      rolesProvider.setRoles({ROLES});
  })

  .factory('sdrConfig', function() {
      return {
          <!-- BEGIN REGISTRATIONID -->
          registrationId: {REGISTRATION_ID},
          <!-- END REGISTRATIONID -->
          makeSearchUrl: function(search) {
              if(search) {
                  return {CLUBSEARCH} + '?callback=JSON_CALLBACK';
              } else {
                  return {CLUBSEARCH} + '?callback=JSON_CALLBACK&search=' + search;
              }
          },
          makePersonSearchUrl: function(search) {
              return {PERSONSEARCH} + '?callback=JSON_CALLBACK&search=' + search;
          },
          clubRegUrl: {NEWCLUBREG},
          ckeditorCss: {CKEDITORCSS},
          userType: {USERTYPE}
      };
  });
</script>
