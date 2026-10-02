<div class="fs-admin__block">
    <h4>{tr:password_hashing_performance}</h4>

    <p>
        This section allows you to see the impact of the config setting
        encryption_password_hash_iterations_new_files on performance. Note
        that this hashing is done for every chunk uploaded. If you have set
        encryption_key_version_new_files to 1 or above and the user is
        using a generated password then this hashing is not performed.
    </p>

    <div class="fs-admin__buttons">
        <button type="button" class="fs-button" data-action="show-password-hashing-performance">{tr:calculate}</button>
    </div>

    <div class="fs-admin__table">
        <table class="fs-table fs-table--striped password-hashing-performance">
            <tr>
                <th>{tr:iterations}</th>
                <th>{tr:time_to_complete_ms}</th>
                <th>{tr:system_active_setting}</th>
            </tr>

            <tr class="tpl">
                <td class="rounds number"></td>
                <td class="milliseconds number"></td>
                <td class="active number"></td>
            </tr>
        </table>
    </div>
</div>

<div class="fs-admin__block">
    <h4>crypto performance</h4>

    <p>
        The below will encrypt and decrypt a chunk of data without sending it anywhere.
        Your current chunk size is {cfg:upload_chunk_size} bytes.
    </p>

    <div class="fs-admin__buttons">
        <button type="button" class="fs-button" data-action="show-chunk-crypto-performance">crypto perf</button>
    </div>

    <div class="fs-admin__table">
        <table class="fs-table fs-table--striped crypto-performance">
            <tr>
                <th>{tr:action}</th>
                <th>{tr:time_to_complete_ms}</th>
            </tr>

            <tr class="tpl">
                <td class="action"></td>
                <td class="milliseconds number"></td>
            </tr>
        </table>
    </div>
</div>

<div class="fs-admin__block">
    <h4>PBKDF2 performance</h4>

    <p>
        The below will derive a key for a series of years to allow you to see how long it takes on this PC.
        Current crypto_pbkdf2_expected_secure_to_year is <?php echo Config::get('crypto_pbkdf2_expected_secure_to_year'); ?>.
        Current hash iteartions is <?php echo Config::get('encryption_password_hash_iterations_new_files'); ?>.
    </p>

    <div class="fs-admin__buttons">
        <button type="button" class="fs-button" data-action="show-pbkdf2-crypto-performance">pbkdf2 perf</button>
    </div>

    <div class="fs-admin__table">
        <table class="fs-table fs-table--striped pbkdf2-performance">
            <tr>
                <th>{tr:epoch_year}</th>
                <th>{tr:iterations}</th>
                <th>{tr:time_to_complete_s}</th>
            </tr>

            <tr class="tpl">
                <td class="year"></td>
                <td class="iterations number"></td>
                <td class="seconds number"></td>
            </tr>
        </table>
    </div>
</div>

<div class="fs-admin__block">
    <h4>StreamSaver file generation</h4>

    <p>
        The below button will create a small file with some text in it.
    </p>

    <div class="fs-admin__buttons">
        <button type="button" class="fs-button" data-action="generate-test-ss">generate file with StreamSaver</button>
    </div>
</div>

<div class="fs-admin__block">
    <h4>Streaming zip64 generation</h4>

    <p>
        The below button will create and save a zip64 file to your downloads directory for format testing.
    </p>

    <div class="fs-admin__buttons">
        <button type="button" class="fs-button" data-action="generate-test-zip64">generate zip64</button>
    </div>
</div>

<div class="fs-admin__block">
    <h4>UI Bootstrap elements</h4>
    <p>
        The buttons allow you to see some bootstrap things by clicking.
    </p>
    <div class="fs-admin__buttons">
        <button type="button" class="fs-button" data-action="show-bs-alert-success">show alert success dialog</button>
        <button type="button" class="fs-button" data-action="show-bs-test">show test dialog</button>
        <button type="button" class="fs-button" data-action="show-bs-test-bootbox">show bootbox test dialog</button>
        <button type="button" class="fs-button" data-action="show-bs-test-alertbs">show ui.alertbs dialog</button>
        <button type="button" class="fs-button" data-action="show-bs-test-error">show ui.error dialog (x)</button>
        <button type="button" class="fs-button" data-toggle="modal" data-target="#exampleModal">Launch demo modal</button>
        <button type="button" class="fs-button" data-action="show-bs-maint1">show maintenance dialog (state)</button>
        <button type="button" class="fs-button" data-action="show-bs-maint2">show maintenance dialog (null)</button>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        This is the contents of the modal.
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary">Save changes</button>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript" src="{path:js/admin_testing.js}"></script>
