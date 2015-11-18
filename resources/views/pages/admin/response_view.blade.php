{{--
|--------------------------------------------------------------------------
| Admin view plan
|--------------------------------------------------------------------------
|
| This template is used when viewing customer.
|
--}}

<div class="panel panel-default">
    <div class="panel-heading">

     @include('pages/admin/partials/viewer_sub_header', [
        'viewerSubHeaderText' => 'Plan details',
        'options' => [
            [
                'url'        => 'admin.plan.view',
                'link_name'  => '',
                'params'     => '',
                'attributes' => ''
            ]
        ]
     ])

    </div>
    <div class="panel-body">

    @include('pages/main/partials/plan', [
        'formAction' => 'admin.dashboard.plan.save',
        'response'   => $response,
    ])

    </div>
</div>