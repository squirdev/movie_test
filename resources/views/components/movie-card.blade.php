<div class="mt-8 border border-white rounded-md p-5">
    <a href="{{route('movies.show', $movie['id'])}}">
      <div>
          <div style="width: 100%; height:300px; overflow: hidden">
              <img src="{{$movie['poster']}}"    alt="parasite"
                   class="hover:opacity-75 transition ease-in-out duration-150 w-full h-full " style="object-fit: cover; object-position: center">
          </div>

          <div class="mt-2">
              <a href="{{route('movies.show', $movie['id'])}}" class="text-lg block text-center w-full mt-2 font-bold hover:text-gray:300">
                  @if(strlen($movie['title'])>15) {{substr($movie['title'],0,15)}}... @else {{$movie['title']}} @endif

              </a>
              <div class="flex items-center text-gray-400 text-sm  my-3 justify-center">
                  <svg class="fill-current text-orange-500 w-4 " viewBox="0 0 24 24"><g data-name="Layer 2"><path d="M17.56 21a1 1 0 01-.46-.11L12 18.22l-5.1 2.67a1 1 0 01-1.45-1.06l1-5.63-4.12-4a1 1 0 01-.25-1 1 1 0 01.81-.68l5.7-.83 2.51-5.13a1 1 0 011.8 0l2.54 5.12 5.7.83a1 1 0 01.81.68 1 1 0 01-.25 1l-4.12 4 1 5.63a1 1 0 01-.4 1 1 1 0 01-.62.18z" data-name="star"/></g></svg>
                  <span class="ml-1 inline-block @if($movie->plan->name == \App\Models\Plan::TYPE_PREMIUM) bg-blue-500 @else bg-red-500 @endif text-white px-2 py-1 rounded-full">@if($movie->plan->name == \App\Models\Plan::TYPE_PREMIUM)Premium @else Basic @endif</span>
                  <span class="mx-2">|</span>
                  <span>{{$movie['release_year']}}</span>

              </div>
              <div class="text-gray-400 text-sm object-fill">
                  <div class="grid grid-cols-8 flex items-center"><span class=" text-right col-span-4">Tag:</span>  <span class="ml-1 col-span-3 text-center inline-block  bg-yellow-500 text-white px-2 py-1 rounded-full">{{$movie->tag}}</span></div>
                  <div class="grid grid-cols-8"><span class=" text-right col-span-4">Rent Start:</span> <span class="pl-3 col-span-4">{{\Carbon\Carbon::parse($movie['rent_start'])->format('Y-m-d')}}</span></div>
                  <div class="grid grid-cols-8"><span class=" text-right col-span-4">Rent End:</span> <span class="pl-3 col-span-4">{{\Carbon\Carbon::parse($movie['rent_end'])->format('Y-m-d')}}</span></div>
                  <div class="grid grid-cols-8"><span class=" text-right col-span-4">Price:</span> <span class="pl-3 col-span-4">{{$movie['rent_price']}}</span></div>

                  <div class="flex justify-center my-2">

                      @if($rented != false)
                          <span class="ml-1 inline-block   bg-red-500 text-white px-10 py-1 rounded-full">Rented</span>
                      @else
                          <span class="ml-1 inline-block bg-blue-500   text-white px-10 py-1 rounded-full">Rent</span>
                      @endif
                  </div>
              </div>

          </div>
      </div>
    </a>
</div>
