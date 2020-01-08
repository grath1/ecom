@extends('layouts.adminLayout.admin_design')
@section('content')

<div id="content">
  <div id="content-header">
    <div id="breadcrumb"> <a href="{{ url('/admin/dashboard')}}" title="Go to Home" class="tip-bottom"><i class="icon-home"></i> Home</a> <a href="#">Products</a> <a href="{{ url('/admin/view-product')}}" class="current">View Products</a> </div>
    <h1>Products</h1>
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
            <h5>View Products</h5>
          </div>
          <div class="widget-content nopadding">
            <table class="table table-bordered data-table">
              <thead>
                <tr>
                  <th>Category ID</th>
                  <th>Category Name</th>
                  <th>Product ID</th>
                  <th>Product Name</th>
                  <th>Product Code</th>
                  <th>Product Color</th>
                  <th>Price</th>
                  <th>Image</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
              	@foreach($products as $product)
                <tr class="gradeX">
                  <td>{{ $product->category_id }}</td>
                  <td>{{ $product->category_name }}</td>
                  <td>{{ $product->id }}</td>
                  <td>{{ $product->product_name }}</td>
                  <td>{{ $product->product_code }}</td>
                  <td>{{ $product->product_color}}</td>
                  <td>${{ $product->price }}</td>
                  <td>
                    @if(!empty($product->image))
                      <img src="{{ asset('images/backend_images/products/small/'.$product->image) }}" style="width:60px;">
                    @endif  
                  </td>
                  @if($product->status == 1)<?php echo '<td style="color: green; font-weight: bold;"> ACTIVE' ?> @else <?php echo '<td style="color: red; font-weight: bold;">INACTIVE' ?> @endif</td>
                  <td class="center"> 
                    <a href="#myModal{{ $product->id }}" data-toggle="modal" class="btn btn-success btn-mini" title="View Product">View</a> 
                    <a href="{{ url('/admin/edit-product/'.$product->id) }}" class="btn btn-primary btn-mini" title="Edit Product">Edit</a> 
                    <a href="{{ url('/admin/add-attributes/'.$product->id) }}" class="btn btn-success btn-mini" title="Add Attributes">Add</a> 
                    <a href="{{ url('/admin/add-images/'.$product->id) }}" class="btn btn-info btn-mini" title="Add Images">Add</a>
                    <a rel="{{ $product->id }}" rel1="delete-product" <?php /*href="{{ url('/admin/delete-product/'.$product->id) }}" */?> href="javascript:" class="btn btn-danger btn-mini deleteRecord" title="Delete Image">Delete</a></td>
                </tr>

                
                  <div id="myModal{{ $product->id }}" class="modal hide">
                    <div class="modal-header">
                      <button data-dismiss="modal" class="close" type="button">×</button>
                      <h3>{{ $product->product_name }} Full Details</h3>
                    </div>
                    <div class="modal-body">
                      <p><strong>Product ID: </strong>{{ $product->id }}</p>
                      <p><strong>Category ID: </strong>{{ $product->category_id }}</p>
                      <p><strong>Product Code: </strong>{{ $product->product_code }}</p>
                      <p><strong>Product Color: </strong>{{ $product->product_color }}</p>
                      <p><strong>Price: </strong>${{ $product->price }}</p>
                      <p><strong>Fabric: </strong></p>
                      <p><strong>Material: </strong>{{ $product->care }}</p>
                      <p><strong>Product Description: </strong>{{ $product->description }}</p>
                    </div>
                  </div>
                
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

@endsection