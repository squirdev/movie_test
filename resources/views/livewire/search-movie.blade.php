<div class="row">
    <div class="flex row justify-end">
        <input
            type="text"
            wire:model.debounce.500ms="search"
            class="bg-gray-800 text-sm rounded-full w-64 px-4 pl-8 py-1 focus:outline-none focus:shadow-outline" placeholder="Search (Press '/' to focus)"
            x-ref="search"
        >
        <div class="row ">
           <div class="float-right">
               {{ $searchResult->links('pagination') }}
           </div>
        </div>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">
        @if(count($searchResult)==0)
           <div class="p-5">
               <p> There is no earch result</p>
           </div>
        @endif
        @foreach($searchResult as $item)
                @if(in_array($item->id , $rented_ids))
                    <x-movie-card :movie="$item" :rented="true"/>
                @else
                    <x-movie-card :movie="$item" :rented="false"/>
                @endif
        @endforeach
    </div>
    <div class="row my-5 ">
            <div class="float-right">
                {{ $searchResult->links('pagination') }}
            </div>
    </div>
</div>

