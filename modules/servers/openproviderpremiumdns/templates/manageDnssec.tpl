<link rel="stylesheet" href="{$cssModuleUrl}">
<script src="{$jsModuleUrl}"></script>
<section class="js-dnssec-module">
    <div class="row d-flex align-items-center justify-content-between mb-3">
        <h2 class="mb-0">Manage DNSSEC Records</h2>
        
        <form id="dnssecToggleForm" class="mb-0 d-flex align-items-center gap-2">
            <input type="hidden" name="id" value="{$serviceId}" />
            <input type="hidden" name="modop" value="custom" />
            <input type="hidden" name="a" value="toggle_dnssec" />
            <button class="btn btn-primary" type="submit" id="dnssecToggleBtn">
                {if $isDnssecEnabled}Deactivate DNSSEC{else}Activate DNSSEC{/if}
            </button>
            <div id="dnssecLoading" class="spinner-border text-primary ml-2" role="status" style="display: none;">
                <span style="display: none;">Loading...</span>
            </div>
        </form>
    </div>

    <div class="dnssec-alert-error-message alert alert-danger hidden">
        <span id="dnssecErrorMessage"></span>
    </div>

    {if ($isDnssecEnabled)}
        <div class="dnssec-alert-on-disabled alert alert-warning hidden">
            DNSSEC is not active on this domain.
        </div>
        <div class="dnssec-alert-on-enabled alert alert-warning">
            DNSSEC is active for this domain. If you deactivate DNSSEC, existing key will be deleted from this premium DNS zone.
        </div>
    {else}        
        <div class="dnssec-alert-on-disabled alert alert-warning">
            DNSSEC is not active on this domain.
        </div>
        <div class="dnssec-alert-on-enabled alert alert-warning hidden">
            DNSSEC is active for this domain. If you deactivate DNSSEC, existing key will be deleted from this premium DNS zone.
        </div>
        <div class="dnssec-alert-on-enabled-new alert alert-warning hidden">
            DNSSEC has not been activated yet. Please activate to add a DNSSEC record for this premium DNS zone.
        </div>
    {/if}

    {if ($isDnssecEnabled)}
        <table class="dnssec-records-table table table-bordered">
    {else}
        <table class="dnssec-records-table table table-bordered hidden">
    {/if}
        <thead>
            <tr>
                <th>Flags</th>
                <th>Algorithm</th>
                <th>Public key</th>
            </tr>
        </thead>
        <tbody>
        <tr>
            <td>{$dnssecKey['flags']}</td>
            <td>{$dnssecKey['alg']}</td>
            <td class="break-word">{$dnssecKey['pubKey']}</td>
        </tr>
        </tbody>
    </table>
</section>
