<?php

declare(strict_types=1);

/*
| Option lists hard-coded in the legacy edit_info tabs (SYSTEM_SPEC §7.7).
| Stored values are the Arabic labels, exactly as the legacy DB holds them.
*/

return [
    'site' => ['داخل النطاق', 'المرحلة الأولى', 'المرحلة الثانية', 'خارج النطاق'],
    'facade' => ['شمال', 'شرق', 'جنوب', 'غرب'],
    'land_nature' => ['مرتفع', 'مستوي', 'منخفض'],
    'street' => ['مسفلتة', 'غير مسفلتة', 'مضاءة', 'غير مضاءة'],
    'neighbor' => ['مبني', 'غير مبني'],
    'include' => [
        'حوائط', 'سلالم', 'زجاج مزدوج', 'جبس بالسقف', 'إضاءة مخفية', 'كراج كهربائي',
        'كراج عادي', 'بوابات', 'مصاعد', 'سخانات', 'حمام عربي', 'حمام إفرنجي',
    ],
    'services' => [
        'electricity' => 'الكهرباء',
        'sanitation' => 'الصرف الصحي',
        'water' => 'المياه',
        'telephone' => 'الهاتف',
        'internet' => 'الإنترنت',
    ],
    'flooring_rooms' => [
        'yards' => 'الأحواش',
        'reception' => 'المجالس',
        'entrance' => 'المداخل',
        'rooms' => 'الغرف',
    ],
    'facade_sides' => [
        'n' => 'الشمالية',
        's' => 'الجنوبية',
        'e' => 'الشرقية',
        'w' => 'الغربية',
    ],
    'architectural' => ['ممتاز', 'جيد', 'رديء'],
    'quality_insulation' => ['لا يوجد', 'حراري', 'مائي', 'مائي حراري'],
    'structure_construction' => ['خرساني', 'حوائط حاملة', 'مباني معدينة', 'مباني خشبية'],
    'bishop_type' => ['خرسانة مسلحة', 'كمرات حديدية', 'كمرات خشبية', 'أخرى'],
    'build_state' => ['ممتاز', 'جيد'],
    'max_build_age' => 60,
    'ac_types' => ['مركزي', 'سبيلت', 'شباك', 'غير مركب', 'مركب', 'توجد التوصيلات فقط'],
    'yes_no' => ['نعم', 'لا'],
    'building_questions' => [
        'populated' => 'هل العقار مأهول',
        'identical' => 'هل العقار مطابق لفسح البناء',
        'contrary' => 'هل العقار مخالف لأنظمة البناء',
        'concrete_roof' => 'هل جميع العقار سقف خرساني مسلح',
        'ready' => 'هل العقار جاهز للسكن',
        'ground_extension' => 'هل يوجد بالعقار ملحق أرضي سقفه شينكو',
    ],
    'question_labels' => [
        'need_cost' => 'هل العقار به بعض التشطيبات لم تكتمل أو تحتاج إلى صيانة أو به شقوق',
        'cost' => 'التكلفة التقديرية',
        'best_use' => 'يعتبر الاستخدام الحالي افضل استخدام',
        'inside_inspection' => 'معاينة العقار من الداخل والخارج',
        'outside_inspection' => 'معاينة العقار من الخارج',
    ],
    'depreciation_types' => [
        1 => 'القسط الثّابت',
        2 => 'العمر الممتدّ',
    ],
    'picture_descriptions' => [
        'الواجهة', 'خارجية', 'الحوش', 'داخلية', 'المطبخ', 'الحمام', 'السطح', 'عداد الكهرباء', 'عداد المياه', 'أخرى',
    ],
    'attachment_types' => ['لا يوجد', 'صورة للصك', 'صورة لطلب التثمين', 'شهادة التسجيل', 'أخرى'],
    'official_report_type' => 'التقرير الرسمي',
    'upload' => [
        'picture_max_kb' => 10240,
        'attachment_max_kb' => 20480,
        'attachment_mimes' => 'pdf,jpg,jpeg,png,webp,doc,docx,xls,xlsx',
    ],
];
