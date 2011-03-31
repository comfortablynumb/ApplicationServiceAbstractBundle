ApplicationServiceAbstractBundle
################################

A bundle that provides base classes to easily create your own services, providing a standard way to handle requests and responses, exceptions, concurrency control, etc.

NOTE: EVERYTHING IS UNDER HEAVY DEVELOPMENT. This is NOT a stable or even usable version yet. This bundle has born in the middle of a complex application I'm developing. I'm working hard to remove everything which is specific to my application so it can become a truly usable bundle for other apps. Comments and help are always welcomed :)

What is it?
-----------

ApplicationServiceAbstractBundle is a generic Service layer. You can create your own services extending from the base ApplicationService class the bundle provides. This lets you, for example, encapsulate all the logic you need for a specific action (exception handling, concurrency control, data binding, etc), and make it reusable across applications without having to copy-and-paste code from your controllers. IE: you can reuse the same CREATE action from your service in your frontend, backend or api sub-applications.

There are lots of functionalities this bundle provides. I'll be adding information to this readme, so stay in touch. In the meantime, you can freely look at the code so you can have an idea of what this bundle can do for you.
