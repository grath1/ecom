<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Image;
use Auth;
use Session;
use App\Category;
use App\Product;
use App\Coupon;
use Illuminate\Support\Facades\Input;
use App\ProductsAttribute;
use App\ProductsImage;
use DB;


class ProductsController extends Controller
{
    public function addProduct(Request $request){

    	if($request->isMethod('post')){
    		$data = $request->all();
    		//echo "<pre>"; print_r($data); die;
    		if(empty($data['category_id'])){
    			return redirect()->back()->with('flash_message_error','Please Select an Option for Under Category!');
    		}
    		$product = new Product;
    		$product->category_id = $data['category_id'];
    		$product->product_name = $data['product_name'];
    		$product->product_code = $data['product_code'];
    		$product->product_color = $data['product_color'];
    		if(!empty($data['description'] && $data['care'])){
    			$product->description = $data['description'];
                $product->care = $data['care'];
    		}else{
    			$product->description = '';
                $product->care = '';
    		}
    		$product->price = $data['price'];

    		// Upload Image
    		if($request->hasfile('image')){
    			$image_tmp = Input::file('image');
    			if($image_tmp->isValid()){
    				$extension = $image_tmp->getClientOriginalExtension();
    				$filename = rand(111,99999).'.'.$extension;
    				$large_image_path = 'images/backend_images/products/large/'.$filename;
    				$medium_image_path = 'images/backend_images/products/medium/'.$filename;
    				$small_image_path = 'images/backend_images/products/small/'.$filename;
    				//Resize Image
    				Image::make($image_tmp)->save($large_image_path);
    				Image::make($image_tmp)->resize(600,600)->save($medium_image_path);
    				Image::make($image_tmp)->resize(300,300)->save($small_image_path);

    				//Store image name in products table
    				$product->image = $filename;
    			}
    		}

    		if(empty($data['status'])){
                $status = 0;
            }else{
                $status = 1;
            }

            $product->status = $status;
    		$product->save();
    		/*return redirect()->back()->with('flash_message_success','Product has been added Successfully!');*/
    		return redirect('/admin/view-product')->with('flash_message_success','Product has been added Successfully!');
    	}
        //Categories drop down start
    	$categories = Category::where(['parent_id'=>0])->get();
    	$categories_dropdown = "<option selected disabled>Select</option>";
    	foreach($categories as $cat){
    		$categories_dropdown .= "<option value='".$cat->id."'>".$cat->name."</option>";
    		$sub_categories = Category::where(['parent_id'=>$cat->id])->get();
    		foreach ($sub_categories as $sub_cat) {
    			$categories_dropdown .= "<option value = '".$sub_cat->id."'>&nbsp;--&nbsp;".$sub_cat->name."</option>";
    		}
    	}
        //Categories drop down Finish
    	return view('admin.products.add_product')->with(compact('categories_dropdown'));
    }

    public function editProduct(Request $request, $id = null){

        if($request->isMethod('post')){
            $data = $request->all();
            //echo "<pre>"; print_r($data); die;

            // Upload Image
            if($request->hasfile('image')){
                $image_tmp = Input::file('image');
                if($image_tmp->isValid()){
                    $extension = $image_tmp->getClientOriginalExtension();
                    $filename = rand(111,99999).'.'.$extension;
                    $large_image_path = 'images/backend_images/products/large/'.$filename;
                    $medium_image_path = 'images/backend_images/products/medium/'.$filename;
                    $small_image_path = 'images/backend_images/products/small/'.$filename;
                    //Resize Image
                    Image::make($image_tmp)->save($large_image_path);
                    Image::make($image_tmp)->resize(600,600)->save($medium_image_path);
                    Image::make($image_tmp)->resize(300,300)->save($small_image_path);
                }
            }else{
                $filename = $data['current_image'];
            }

            if(empty($data['description'])){
                $data['description'] = '';
            }

            if(empty($data['care'])){
                $data['care'] = '';
            }

            if(empty($filename)){
                $filename = '';
            } 

            if(empty($data['status'])){
                $status = 0;
            }else{
                $status = 1;
            }


            Product::where(['id'=>$id])->update(['category_id'=>$data['category_id'],'product_name'=>$data['product_name'],'product_code'=>$data['product_code'],'product_color'=>$data['product_color'],'description'=>$data['description'],'care'=>$data['care'],'price'=>$data['price'],'image'=>$filename,'status'=>$status]);
            return redirect('admin/view-product')->with('flash_message_success','Product has been updated Successfully!');
        }

        //Get Product Details

        //echo "test"; die;
        $productDetails = Product::where(['id'=>$id])->first();

        //Categories drop down start
        $categories = Category::where(['parent_id'=>0])->get();
        $categories_dropdown = "<option selected disabled>Select</option>";
        foreach($categories as $cat){
            if($cat->id==$productDetails->category_id){
                $selected = "selected";

            }else{
                $selected = "";
            }
            $categories_dropdown .= "<option value='".$cat->id."'".$selected.">".$cat->name."</option>";
            $sub_categories = Category::where(['parent_id'=>$cat->id])->get();
            foreach ($sub_categories as $sub_cat) {
                if($sub_cat->id==$productDetails->category_id){
                $selected = "selected";

                }else{
                    $selected = "";
                }
                $categories_dropdown .= "<option value = '".$sub_cat->id."' ".$selected.">&nbsp;--&nbsp;".$sub_cat->name."</option>";
            }
        }
        //Categories drop down Finish


        return view('admin.products.edit_product')->with(compact('productDetails','categories_dropdown'));
    }

    public function viewProducts(){
    	$products = Product::orderby('id', 'DESC')->get();
    	$products = json_decode(json_encode($products));
        //echo "<pre>"; print_r($products); die;
    	foreach($products as $key => $val){
            $category_name = Category::where(['id'=>$val->category_id])->first();
            Category::where(['id'=>$val->category_id]);
            $products[$key]->category_name = $category_name->name;             
        
    	}
    	
    	return view('admin.products.view_products')->with(compact('products'));
            }
    }

    public function addImages(Request $request,$id = null){
        $productDetails = Product::with('attributes')->where(['id'=>$id])->first();

        if ($request->isMethod('post')) {
            // Add Images 
            $data = $request->all();
            //echo "<pre>"; print_r($data); die;
            if($request->hasFile('image')){
                $files = $request->file('image');
                foreach ($files as $file) {         
                    // Upload Images after resize
                    $image = new ProductsImage;
                    $extension = $file->getClientOriginalExtension();
                    $fileName = rand(111, 99999).'.'.$extension;
                    $large_image_path = 'images/backend_images/products/large/'.$fileName;
                    $medium_image_path = 'images/backend_images/products/medium/'.$fileName;
                    $small_image_path = 'images/backend_images/products/small/'.$fileName;
                    Image::make($file)->save($large_image_path);
                    Image::make($file)->resize(600,600)->save($medium_image_path);
                    Image::make($file)->resize(300,300)->save($small_image_path);
                    $image->image = $fileName;
                    $image->product_id = $data['product_id'];
                    $image->save();   
                }
            }
            return redirect('admin/add-images/'.$id)->with('flash_message_success','Product Images has been added Successfully!');
        }

        $productsImg = ProductsImage::where(['product_id'=>$id])->get();
        $productsImg = json_decode(json_encode($productsImg));

        $productsImages = "";
        foreach($productsImg as $img){
            $productsImages .= 
            "<tr>
              <td>".$img->id."</td>
              <td>".$img->product_id."</td>
              <td><img width='100px' src='/images/backend_images/products/small/$img->image'>
              </td>
              <td>
                <a rel='$img->id' rel1='delete-alt-image' href='javascript:' class='btn btn-danger btn-mini deleteRecord' title='Delete Image'>Delete</a>
              </td>
            </tr>";
        }

        return view('admin.products.add_images')->with(compact('productDetails', 'productsImages'));
    }

    public function deleteProductImage($id = null){

        // Get Product Image Name
        $productImage = Product::where(['id'=>$id])->first();

        // Get Product Image Paths
        $large_image_path = 'images/backend_images/products/large/';
        $medium_image_path = 'images/backend_images/products/medium/';
        $small_image_path = 'images/backend_images/products/small/';

        // Delete Large Image if not exists in folder
        if(file_exists($large_image_path.$productImage->image)){
            unlink($large_image_path.$productImage->image);
        }

        // Delete Large Image if not exists in folder
        if(file_exists($medium_image_path.$productImage->image)){
            unlink($medium_image_path.$productImage->image);
        }

        // Delete Large Image if not exists in folder
        if(file_exists($small_image_path.$productImage->image)){
            unlink($small_image_path.$productImage->image);
        }

        // Delete Image from Products table

        Product::where(['id'=>$id])->update(['image'=>'']);
        return redirect()->back()->with('flash_message_success', 'Product Image has been deleted Successfully!');
    }

    public function deleteAltImage($id = null){

        // Get Product Image Name
        $productImage = ProductsImage::where(['id'=>$id])->first();

        // Get Product Image Paths
        $large_image_path = 'images/backend_images/products/large/';
        $medium_image_path = 'images/backend_images/products/medium/';
        $small_image_path = 'images/backend_images/products/small/';

        // Delete Large Image if not exists in folder
        if(file_exists($large_image_path.$productImage->image)){
            unlink($large_image_path.$productImage->image);
        }

        // Delete Large Image if not exists in folder
        if(file_exists($medium_image_path.$productImage->image)){
            unlink($medium_image_path.$productImage->image);
        }

        // Delete Large Image if not exists in folder
        if(file_exists($small_image_path.$productImage->image)){
            unlink($small_image_path.$productImage->image);
        }

        // Delete Image from Products table

        ProductsImage::where(['id'=>$id])->delete();
        return redirect()->back()->with('flash_message_success', 'Product Alternate  Image(s) has been deleted Successfully!');
    }

    public function deleteProduct($id = null){
        Product::where(['id'=>$id])->delete();
        return redirect()->back()->with('flash_message_success','Product has been Successfully deleted!');
    }

    public function addAttributes(Request $request,$id = null){
        $productDetails = Product::with('attributes')->where(['id'=>$id])->first();
        //$productDetails = json_decode(json_encode($productDetails));
        //echo "<pre>"; print_r($productDetails); die;
        if ($request->isMethod('post')) {
            $data = $request->all();
            //echo "<pre>"; print_r($data); die;

            foreach ($data['sku'] as $key => $val) {
                if (!empty($val)) {
                    // Prevent duplicate SKU Check
                    $attrCountSKU = ProductsAttribute::where('sku',$val)->count();
                    if($attrCountSKU>0){
                        return redirect('admin/add-attributes/'.$id)->with('flash_message_error','SKU already exists! Please add another SKU!!');
                    }

                    // Prevent duplicate Size Check
                    $attrCountSizes = ProductsAttribute::where(['product_id'=>$id, 'size'=>$data['size'][$key]])->count();
                    if($attrCountSizes>0){
                        return redirect('admin/add-attributes/'.$id)->with('flash_message_error',''.$data['size'][$key].' Size already exists for this product! Please add another Size!!');
                    }


                    $attribute = new ProductsAttribute;
                    $attribute->product_id = $id;
                    $attribute->sku = $val;
                    $attribute->size = $data['size'][$key];
                    $attribute->price = $data['price'][$key];
                    $attribute->stock = $data['stock'][$key];
                    $attribute->save();
                }
            }

            return redirect('admin/add-attributes/'.$id)->with('flash_message_success','Product Attributes has been added Successfully!!');
        }
        return view('admin.products.add_attributes')->with(compact('productDetails'));
    }

    public function editAttributes(Request $request,$id = null){
        if($request->isMethod('post')){
            $data = $request->all();
            //echo "<pre>"; print_r($data); die;

            foreach ($data['idAttr'] as $key => $attr) {
                ProductsAttribute::where(['id'=>$data['idAttr'][$key]])->update(['price'=>$data['price'][$key],'stock'=>$data['stock'][$key]]); 
            }
            return redirect()->back()->with('flash_message_success', 'Products Attributes has been Successfully');        }
    }

    public function deleteAttribute($id = null){
        ProductsAttribute::where(['id'=>$id])->delete();
        return redirect()->back()->with('flash_message_success','Attribute has been deleted Successfully!');
    }

    public function products($url = null){

        // Show 404 page if Category url does not exist
        $countCategory = Category::where(['url'=>$url,'status'=>1])->count();
        if($countCategory == 0){
            abort(404);
        }


        // Get all Categories and Subcategories
        $categories = Category::with('categories')->where(['parent_id'=>0])->get();

        $categoryDetails = Category::where(['url'=>$url])->first();

        if($categoryDetails->parent_id==0){
            //If url is main category url
            $sub_Categories = Category::where(['parent_id'=>$categoryDetails->id])->get();

            foreach ($sub_Categories as $subcat) {
                $cat_ids[] = $subcat->id;
            }
            //print_r($cat_ids); die;
            $productsAll = Product::whereIn('category_id',$cat_ids)->where('status',1)->get();
            $productsAll = json_decode(json_encode($productsAll));
            //echo "<pre>"; print_r($productsAll); die;
        }else{
            //If url is sub category url
            $productsAll = Product::where(['category_id'=>$categoryDetails->id])->where('status',1)->get();
        }

        return view('products.listing')->with(compact('categories','categoryDetails','productsAll'));
    }

    public function product($id = null){

        // Show 404 page if product is disabled
        $productsCount = Product::where(['id'=>$id, 'status'=>1])->count();
        if($productsCount == 0){
            abort(404);
        }

        // Get Product Details
        $productDetails = Product::with('attributes')->where('status',1)->where('id',$id)->first();
        $productDetails = json_decode(json_encode($productDetails));

        $relatedProducts = Product::where('id','!=',$id)->where(['category_id'=>$productDetails->category_id])->where('status',1)->get();

        //$relatedProducts = json_decode(json_encode($relatedProducts));

        /*foreach ($relatedProducts->chunk(3) as $chunk) {
            foreach($chunk as $item){
                echo $item; echo "<br>";
            }
            echo "<br><br><br>";
        }
        die;*/
        
        // echo "<pre>"; print_r($relatedProducts); die;

        // Get all Categories and Subcategories
        $categories = Category::with('categories')->where(['parent_id'=>0])->get();
        //echo "<pre>"; print_r($categories); die;

        // Get Product Alternate Images
        $productAltImages = ProductsImage::where('product_id',$id)->get();
        //$productAltImages = json_decode(json_encode($productAltImages));
        /*echo "<pre>"; print_r($productAltImages); die;*/

        $total_stock = ProductsAttribute::where('product_id', $id)->sum('stock');
        //echo "<pre>"; print_r($total_stock); die;
        return view('products.detail')->with(compact('productDetails','categories','productAltImages','total_stock','relatedProducts'));

    }

    public function getProductPrice(Request $request){
        $data = $request->all();

        //echo "<pre>"; print_r($data); die;

        $proArr = explode("-", $data['idSize']);
        //echo $proArr[0]; die;

        $proAttr = ProductsAttribute::where(['product_id' => $proArr[0], 'size' => $proArr[1]])->first();

        echo $proAttr->price;
        echo "#";
        echo $proAttr->stock;
    }

    public function addtocart(Request $request){

        Session::forget('CouponAmount');
        Session::forget('CouponCode');

        $data = $request->all();
        //echo "<pre>"; print_r($data); die;

        // Check Product Stock is available or not
        $product_size = explode("-",$data['size']);

        //echo "<pre>"; print_r($product_size); die;

        $getProductStock = ProductsAttribute::where(['product_id'=>$data['product_id'],'size'=>$product_size[1]])->first();


        //echo "<pre>"; print_r($getProductStock->size); die;

        /*if($getProductStock->size == "Select Size"){
            return redirect()->back()->with('flash_message_error','Please select a Size!');
        } */       
        


        if($getProductStock->stock<$data['quantity']){
            return redirect()->back()->with('flash_message_error','Required Quantity is not Available!');
        }

        if(empty($data['user_email'])){
            $data['user_email'] = '';
        }

        $session_id = Session::get('session_id');
        if(empty($session_id)){
            $session_id = str_random(40);
            // echo $session_id; die;
            Session::put('session_id',$session_id);
        }

        $sizeArr = explode('-',$data['size']);

        $countProducts = DB::table('cart')->where(['product_id'=>$data['product_id'],'product_color'=>$data['product_color'],'size'=>$sizeArr[1],'session_id'=>$session_id])->count();

        if($countProducts>0){
            return redirect()->back()->with('flash_message_error','Product already exist in Cart!');
        }else{

            $getSKU = ProductsAttribute::select('sku')->where(['product_id'=>$data['product_id'],'size'=>$sizeArr[1]])->first();

            DB::table('cart')->insert(['product_id'=>$data['product_id'],'product_name'=>$data['product_name'],'product_code'=>$getSKU->sku,'product_color'=>$data['product_color'],'price'=>$data['price'],'size'=>$sizeArr[1],'quantity'=>$data['quantity'],'user_email'=>$data['user_email'],'session_id'=>$session_id]);
        }

        return redirect('cart')->with('flash_message_success','Product has been added in Cart!');
    }

    public function cart(){
        $session_id = Session::get('session_id');
        $userCart = DB::table('cart')->where(['session_id'=>$session_id])->get();
        foreach ($userCart as $key => $product) {
            $productDetails = Product::where('id',$product->product_id)->first();
            $userCart[$key]->image = $productDetails->image;
        }
        /*echo "<pre>"; print_r($userCart); die;*/
        return view('products.cart')->with(compact('userCart'));
    }

    public function deleteCartProduct($id = null){
        Session::forget('CouponAmount');
        Session::forget('CouponCode');
        DB::table('cart')->where('id',$id)->delete();
        return redirect('cart')->with('flash_message_success','Product has been deleted from Cart!');
    }

    public function updateCartQuantity($id = null,$quantity = null){

        Session::forget('CouponAmount');
        Session::forget('CouponCode');

        $getCartDetails = DB::table('cart')->where('id',$id)->first();
        $getAttributeStock = ProductsAttribute::where('sku',$getCartDetails->product_code)->first();
        echo $getAttributeStock->stock; echo "--";
        $updated_quantity = $getCartDetails->quantity+$quantity;
        if($getAttributeStock->stock >= $updated_quantity){
            DB::table('cart')->where('id',$id)->increment('quantity',$quantity);
        return redirect('cart')->with('flash_message_success','Product Quantity has been updated Successfully!');
        }else{
            return redirect('cart')->with('flash_message_error','Required Product Quantity is not Available!');
        }
        
    }

    public function applyCoupon(Request $request){

        Session::forget('CouponAmount');
        Session::forget('CouponCode');

        $data = $request->all();
        // echo "<pre>"; print_r($data); die;
        $couponCount = Coupon::where('coupon_code', $data['coupon_code'] )->count();
        if($couponCount == 0){
            return redirect()->back()->with('flash_message_error','Coupon is not valid!!');
        }else{
            // Perform other checks like Active/Inactive, Expiry..

            //Get Coupon Details
            $couponDetails = Coupon::where('coupon_code',$data['coupon_code'])->first();
            //echo "<pre>"; print_r($couponDetails); die;

            // if coupon is Inactive
            if ($couponDetails->status == 0) {
                return redirect()->back()->with('flash_message_error','This Coupon is Inactive!!');
            }

            // If coupon is Expired
            $expiry_date = $couponDetails->expiry_date;
            $current_date = date('Y-m-d');

            if($expiry_date < $current_date){
                return redirect()->back()->with('flash_message_error','This Coupon is Expired!!');
            }

            // Coupon Valid for Discount

            // Get Cart Total Amount
            $session_id = Session::get('session_id');
            $userCart = DB::table('cart')->where(['session_id'=>$session_id])->get();
            $total_amount = 0;
            foreach ($userCart as $item) {
                $total_amount = $total_amount + ($item->price * $item->quantity);
            }
            // Check if amount type is Fixed or Percentage

            if ($couponDetails->amount_type=="Fixed") {
                $couponAmount = $couponDetails->amount;
            }else{
                $couponAmount = $total_amount*($couponDetails->amount/100);
            }

            // Add Coupon Code & Amount in Session
            Session::put('CouponAmount',$couponAmount);
            Session::put('CouponCode',$data['coupon_code']);

            return redirect()->back()->with('flash_message_success','Coupon Code Successfully Applied. Enjoy the discount!!');
        }

    }
}
