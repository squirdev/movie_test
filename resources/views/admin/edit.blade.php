@extends('layouts/contentLayoutMaster')

@section('page-style')
@endsection

@section('content')
    <div class="row">
        <div class="col-xl-3 mx-auto">
            <h6 class="mb-0 text-uppercase">Poster View</h6>
            <hr/>
            <div class="card border-top border-0 border-4 border-white">
                <div class="card-body">
                    <div class="border p-4 rounded">
                        <div class="card-title d-flex align-items-center">

                            <h5 class="mb-0 text-white">Poster</h5>
                        </div>
                        <hr/>
                        <div class="row mb-3">
                           <img src="@if(isset($movie) && $movie['poster']) {{$movie['poster']}} @else {{asset(mix('/vendors/admin/images/avatars/avatar-1.png'))}}@endif"/>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-9 mx-auto">
            <h6 class="mb-0 text-uppercase">Edit Movie</h6>
            <hr/>
            <div class="card border-top border-0 border-4 border-white">
                <div class="card-body">
                    <div class="border p-4 rounded">
                        <div class="card-title d-flex align-items-center">

                            <h5 class="mb-0 text-white">Movie Information</h5>
                        </div>
                        <hr/>
                        <form   @if(isset($movie['id'])) action="{{route('admin.movies.update',$movie['id'])}}" @else action="{{route('admin.movies.store')}}"  @endif method="post">
                            @if(isset($movie['id']))
                                {{ method_field('PUT') }}
                            @endif
                            @csrf
                        <div class="row mb-3">
                            <label for="title" class="col-sm-3 col-form-label">Title</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="@if(isset($movie)){{$movie['title']}}@endif" placeholder="Title">
                            </div>
                            @error('title')
                            <p><small class="text-danger">{{ $message }}</small></p>
                            @enderror
                        </div>
                        <div class="row mb-3">
                            <label for="poster" class="col-sm-3 col-form-label">Poster</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control @error('poster') is-invalid @enderror" id="poster"  name ="poster" placeholder="Poster Url" value="@if(isset($movie))  {{$movie['poster']}} @endif">
                            </div>
                            @error('poster')
                            <p><small class="text-danger">{{ $message }}</small></p>
                            @enderror
                        </div>
                        <div class="row mb-3">
                            <label for="rent_price" class="col-sm-3 col-form-label">Price</label>
                            <div class="col-sm-9">
                                <input type="number" class="form-control @error('rent_price') is-invalid @enderror" id="rent_price"  name ="rent_price" value="@if(isset($movie)){{$movie['rent_price']}}@endif" placeholder="Please input rent price">
                            </div>
                            @error('rent_price')
                            <p><small class="text-danger">{{ $message }}</small></p>
                            @enderror
                        </div>
                        <div class="row mb-3">
                            <label for="tag" class="col-sm-3 col-form-label">Tag</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control @error('tag') is-invalid @enderror" id="tag" name="tag" value="@if(isset($movie)){{$movie['tag']}}@endif" placeholder=" movie or music">
                            </div>
                            @error('tag')
                            <p><small class="text-danger">{{ $message }}</small></p>
                            @enderror
                        </div>
                        <div class="row mb-3">
                            <label for="release_year" class="col-sm-3 col-form-label">Release Year</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control @error('release_year') is-invalid @enderror" id="release_year" name ="release_year" value="@if(isset($movie)) {{$movie['release_year']}} @endif" placeholder="2023">
                            </div>
                            @error('release_year')
                            <p><small class="text-danger">{{ $message }}</small></p>
                            @enderror
                        </div>
                        <div class="row mb-3">
                            <label for="rent_period" class="col-sm-3 col-form-label">Rent Period</label>
                            <div class="col-sm-3">
                                <label class="form-label">Select Rent Start</label>
                                <input type="text" class="form-control datepicker @error('rent_start') is-invalid @enderror" value="@if(isset($movie)) {{$movie['rent_start']}}  @endif" name="rent_start" />
                            </div>
                            <div class="col-sm-3">
                                <label class="form-label">Select Rent End</label>
                                <input type="text" class="form-control datepicker  @error('rent_end') is-invalid @enderror" value="@if(isset($movie)) {{$movie['rent_end']}} @endif" name="rent_end" />
                            </div>
                            @error('rent_start')
                            <p><small class="text-danger">{{ $message }}</small></p>
                            @enderror
                            @error('rent_end')
                            <p><small class="text-danger">{{ $message }}</small></p>
                            @enderror
                        </div>
                        <div class="row mb-3">
                            <label for="cast" class="col-sm-3 col-form-label">Plan</label>
                            <div class="col-sm-7">
                                <select class="single-select @error('plan') is-invalid @enderror " name="plan">
                                    @foreach($plans as $plan)
                                        <option value="{{$plan->id}}" @if(isset($movie)&&($movie['plan_id']==$plan->id)) selected @endif>{{$plan->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('plan')
                            <p><small class="text-danger">{{ $message }}</small></p>
                            @enderror
                        </div>
                                <div class="row mb-3">
                                    <div class="col-sm-3"> <label class="form-check-label" for="status">status</label></div>
                                    <div class="col-sm-3 form-check form-switch">
                                        <input class="form-check-input "   type="hidden" id="status" name="status" value="false" >
                                        <input class="form-check-input @error('status') is-invalid @enderror"   type="checkbox" id="status" name="status"  @if(isset($movie)&&$movie['status']) checked   @endif>
                                    </div>
                                    @error('status')
                                    <p><small class="text-danger">{{ $message }}</small></p>
                                    @enderror
                                </div>
                        <div class="row mb-3">
                            <label for="cast" class="col-sm-3 col-form-label">Cast</label>
                            <div class="col-sm-9">
                                <textarea class="form-control @error('cast') is-invalid @enderror" id="cast" name ="cast" rows="3" value placeholder="Actors">
                                   @if(isset($cast))
                                    {{$cast->names}}
                                    @endif

                                </textarea>
                            </div>
                            @error('cast')
                            <p><small class="text-danger">{{ $message }}</small></p>
                            @enderror
                        </div>

                        <div class="row">
                            <label class="col-sm-3 col-form-label"></label>
                            <div class="col-sm-3">
                                <button type="submit" class="btn btn-light px-5">Save</button>
                            </div>
                            <div class="col-sm-3">
                                <a href="{{route('admin.movies.index')}}" class="btn btn-light px-5">Back</a>
                            </div>
                        </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection

@section('add_plugins')
    <script src="{{asset(mix('/vendors/admin/plugins/datetimepicker/js/legacy.js'))}}"></script>
    <script src="{{asset(mix('/vendors/admin/plugins/datetimepicker/js/picker.js'))}}"></script>
    <script src="{{asset(mix('/vendors/admin/plugins/datetimepicker/js/picker.time.js'))}}"></script>
    <script src="{{asset(mix('/vendors/admin/plugins/datetimepicker/js/picker.date.js'))}}"></script>
    <script src="{{asset(mix('/vendors/admin/plugins/bootstrap-material-datetimepicker/js/moment.min.js'))}}"></script>
    <script src="{{asset(mix('/vendors/admin/plugins/bootstrap-material-datetimepicker/js/bootstrap-material-datetimepicker.min.js'))}}"></script>
    <script>
        $('.datepicker').pickadate({
            selectMonths: true,
            selectYears: true
        })
        $(function () {
            $('#date-time').bootstrapMaterialDatePicker({
                format: 'YYYY-MM-DD HH:mm'
            });
            $('#date').bootstrapMaterialDatePicker({
                time: false
            });
            $('#time').bootstrapMaterialDatePicker({
                date: false,
                format: 'HH:mm'
            });
            $('.single-select').select2({
                theme: 'bootstrap4',
                width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' : 'style',
                placeholder: $(this).data('placeholder'),
                allowClear: Boolean($(this).data('allow-clear')),
            });
        });
    </script>
@endsection

@section('add_page_script')
    <script src="{{ asset(mix('vendors/admin/js/index.js')) }}"></script>
@endsection


