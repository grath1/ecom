@extends('layouts.adminLayout.admin_design')
@section('content')

<div id="content">
  <div id="content-header">
    <div id="breadcrumb"> <a href="{{ url('/admin/dashboard')}}" title="Go to Home" class="tip-bottom"><i class="icon-home"></i> Home</a> <a href="#">Coupons</a> <a href="{{ url('/admin/view-product')}}" class="current">View Coupons</a> </div>
    <h1>Coupons</h1>
  </div>
  <div class="container-fluid">
    <hr>
    @if(Session::has('flash_message_error'))
      <div class="alert alert-error alert-block">
        <button type="button" class="close" data-dismiss="alert">x</button>
        <strong>{!! session('flash_message_error') !!}</strong>
      </div>
    @endif
    @if(Session::has('flash_message_success'))
      <div class="alert alert-success alert-block">
        <button type="button" class="close" data-dismiss="alert">x</button>
        <strong>{!! session('flash_message_success') !!}</strong>
      </div>
    @endif
    <div class="row-fluid">
      <div class="span12">

        <div class="widget-box">
          <div class="widget-title"> <span class="icon"><i class="icon-th"></i></span>
            <h5>View Coupons</h5>
          </div>
          <div class="widget-content nopadding">
            <table class="table table-bordered data-table">
              <thead>
                <tr>
                  <th>Coupon ID</th>
                  <th>Coupon Code</th>
                  <th>Amount</th>
                  <th>Amount Type</th>
                  <th>Expiry Date</th>
                  <th>Created Date</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              @foreach($coupons as $coupon)
                <tr class="gradeX">
                  <td>{{ $coupon->id }}</td>
                  <td>{{ $coupon->coupon_code }}</td>
                  <td>
                    @if($coupon->amount_type == "Fixed") $@endif
                    {{ $coupon->amount }}
                    @if($coupon->amount_type == "Percentage")% @endif
                  </td>
                  <td>{{ $coupon->amount_type }}</td>
                  <?php $current_date = date('Y-m-d'); ?>
                    @if($coupon->expiry_date < $current_date)
                      <td style="color:red; font-weight: bold;">EXPIRED</td>
                      @else
                        <td style="color: green; font-weight: bold;">{{ $coupon->expiry_date }}</td>
                      @endelse
                    @endif

                  <td>{{ $coupon->created_at }}</td>
                  @if($coupon->status == 1)<?php echo '<td style="color: green; font-weight: bold;"> ACTIVE' ?> @else <?php echo '<td style="color: red; font-weight: bold;">INACTIVE' ?> @endif</td>
                  <td>
                    <a href="{{ url('/admin/edit-coupon/'.$coupon->id) }}" class="btn btn-primary btn-mini" title="Edit Product">Edit</a> 
                    <a rel="{{ $coupon->id }}" rel1="delete-coupon" href="javascript:" class="btn btn-danger btn-mini deleteRecord" title="Delete Coupon">Delete</a>
                  </td>
                </tr>
              @endforeach  
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

@endsection