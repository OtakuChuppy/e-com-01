@extends('layouts.store')
@section('title', ($page === 'shipping' ? 'ডেলিভারি' : ($page === 'returns' ? 'রিটার্ন' : 'গোপনীয়তা')).' নীতি — '.config('shop.name'))
@section('content')
<section class="max-w-3xl mx-auto px-4 sm:px-6 pt-8">
    @if ($page === 'shipping')
    <h1 class="font-display text-3xl tracking-tight">ডেলিভারি তথ্য</h1>
    <div class="text-[15px] leading-relaxed mt-4 space-y-3 text-ink/80">
        <p>আমরা ক্যাশ অন ডেলিভারির মাধ্যমে সারা বাংলাদেশে ডেলিভারি দিই। ঢাকার ভিতরে ডেলিভারি খরচ ৳60 ও ঢাকার বাইরে ৳120 — অর্ডার কনফার্ম করার আগেই এটি দেখানো হয়।</p>
        <p>অর্ডার ফোনে কনফার্ম করা হয়। আপনার জেলা ও কুরিয়ারের ধারণক্ষমতার উপর ডেলিভারির সময় নির্ভর করে — পার্চেল ছাড়ার পর আপডেট জানাব।</p>
    </div>
    @elseif ($page === 'returns')
    <h1 class="font-display text-3xl tracking-tight">রিটার্ন ও বাতিলকরণ</h1>
    <div class="text-[15px] leading-relaxed mt-4 space-y-3 text-ink/80">
        <p>পার্চেল ছাড়ার আগে অর্ডার রেফারেন্স ও ফোন নম্বরসহ আমাদের সাথে যোগাযোগ করে অর্ডার বাতিল করতে পারেন।</p>
        <p>পণ্য ক্ষতিগ্রস্ত বা ভুল পৌঁছালে ৪৮ ঘণ্টার মধ্যে ছবিসহ আমাদের জানান। যাচাইয়ের পর রিপ্লেসমেন্ট বা রিফান্ডের ব্যবস্থা করা হবে।</p>
    </div>
    @else
    <h1 class="font-display text-3xl tracking-tight">গোপনীয়তা</h1>
    <div class="text-[15px] leading-relaxed mt-4 space-y-3 text-ink/80">
        <p>অর্ডার সম্পন্ন করতে যা প্রয়োজন তা-ই সংগ্রহ করি: নাম, ফোন, ডেলিভারি ঠিকানা ও অর্ডারের তথ্য। আপনার তথ্য আমরা কখনো বিক্রি করি না।</p>
        <p>অর্ডার লুকআপের জন্য রেফারেন্স ও ফোন নম্বর — দুটোই লাগে। অ্যানালিটিক্স চালু থাকলেও সেখানে আপনার ব্যক্তিগত তথ্য যায় না।</p>
    </div>
    @endif
</section>
@endsection
